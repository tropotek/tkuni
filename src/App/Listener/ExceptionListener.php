<?php
namespace App\Listener;

use Tk\Event\ExceptionEvent;
use Tk\EventDispatcher\SubscriberInterface;
use Psr\Log\LoggerInterface;
use Tk\Response;


/**
 * Class RouteListener
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class ExceptionListener implements SubscriberInterface
{
    

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger A LoggerInterface instance
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }


    /**
     * 
     * @param ExceptionEvent $event
     */
    public function onException(ExceptionEvent $event)
    {   
        // TODO: log all errors and send a compiled message periodically (IE: daily, weekly, monthly)
        // This would stop mass emails on major system failures and DOS attacks...

        $config = \App\Factory::getConfig();
        $e = $event->getException();

        try {
            if ($config->get('site.email')) {
                $body = $event->getResponse()->getBody();
                $subject = $config->get('site.title') . ' Error `' . $e->getMessage() . '`';
                $from = $to = $config->get('site.email');
                $message = new \Tk\Mail\Message($body, $subject, $from, $to);
                $message->send();
            }
        } catch (\Exception $ee) { $this->logger->warning($ee->getMessage()); }

        
    }


    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            \Tk\Kernel\KernelEvents::EXCEPTION => 'onException'
        );
    }
    
    
}