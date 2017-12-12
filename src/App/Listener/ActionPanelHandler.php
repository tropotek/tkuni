<?php
namespace App\Listener;

use Tk\Event\Subscriber;

/**
 * This object helps cleanup the structure of the controller code
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class ActionPanelHandler implements Subscriber
{

    public function onShow(\Tk\Event\Event $event)
    {
        $controller = $event->get('controller');
        if ($controller instanceof \App\Controller\AdminIface && $controller->getActionPanel()) {
            $controller->getTemplate()->prependTemplate($controller->getTemplate()->getRootElement(), $controller->getActionPanel()->show());
        }
    }
    
    /**
     * getSubscribedEvents
     * 
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            \Tk\PageEvents::CONTROLLER_SHOW =>  array('onShow', 0)
        );
    }

}