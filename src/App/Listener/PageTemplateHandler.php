<?php
namespace App\Listener;

use Tk\Event\Subscriber;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class PageTemplateHandler implements Subscriber
{

    /**
     * @param \Tk\Event\Event $event
     */
    public function onPageInit(\Tk\Event\Event $event)
    {
        /** @var \App\Controller\Iface $controller */
        $controller = $event->get('controller');
        $config = \App\Factory::getConfig();

        $role = 'public';
        if ($config->getRequest()->getAttribute('role'))
            $role = $config->getRequest()->getAttribute('role');
        $templatePath = $config['template.' . $role];

        // Setup the template loader
        $controller->getPage()->setTemplatePath($config->getSitePath() . $templatePath);
        \App\Factory::getDomLoader();
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            \Tk\PageEvents::PAGE_INIT => 'onPageInit'
        );
    }

}