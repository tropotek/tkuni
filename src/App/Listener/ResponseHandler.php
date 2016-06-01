<?php
namespace App\Listener;


use Tk\EventDispatcher\SubscriberInterface;
use Tk\Event\ControllerResultEvent;
use Tk\Event\FilterResponseEvent;
use Tk\Kernel\KernelEvents;
use Tk\Response;


/**
 * Class ShutdownHandler
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class ResponseHandler implements SubscriberInterface
{

    /**
     * @var \Dom\Modifier\Modifier
     */
    protected $domModifier = null;

    /**
     * ResponseHandler constructor.
     *
     * @param \Dom\Modifier\Modifier $domModifier
     */
    public function __construct($domModifier = null)
    {
        $this->domModifier = $domModifier;
    }

    /**
     * domModify 
     *
     * @param ControllerResultEvent $event
     */
    public function domModify(ControllerResultEvent $event)
    {
        if (!$this->domModifier) return;
        
        /* @var $template \Dom\Template */
        $result = $event->getControllerResult();
        if ($result instanceof \Dom\Renderer\Iface) {
            $result = $result->getTemplate()->getDocument();
        }
        if ($result instanceof \Dom\Template) {
            $result = $result->getDocument();
        }
        if ($result instanceof \DOMDocument) {
            $this->domModifier->execute($result);
        }
    }

    /**
     * NOTE: if you want to modify the template using its API
     * you must add the listeners before this one its priority is set to -100
     * make sure your handlers have a priority > -100 so this is run last
     * 
     * Convert controller return types to a request
     * Once this event is fired and a response is set it will stop propagation, 
     * so other events using this name must be run with a priority > -100
     * 
     * @param ControllerResultEvent $event
     */
    public function convertControllerResult(ControllerResultEvent $event)
    {
        $result = $event->getControllerResult();
        
        if ($result instanceof \Dom\Template) {
            $event->setResponse(new Response($result->toString()));
        } else if ($result instanceof \Dom\Renderer\Iface) {
            $event->setResponse(new Response($result->getTemplate()->toString()));
        } else if (is_string($result)) {
            $event->setResponse(new Response($result));
        }
    }

    /**
     * Add any headers to the final response.
     * 
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        
        // disable the browser cache as this is a dynamic site.
        $response->addHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->addHeader('Cache-Control', 'post-check=0, pre-check=0');
        $response->addHeader('Expires', 'Mon, 1 Jan 2000 00:00:00 GMT');
        $response->addHeader('Pragma', 'no-cache');
        $response->addHeader('Last-Modified', gmdate("D, d M Y H:i:s") . " GMT");
        
    }

    /**
     * getSubscribedEvents
     * 
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::VIEW => array(array('domModify', -80), array('convertControllerResult', -100)),
            KernelEvents::RESPONSE => 'onResponse'
        );
    }
}