<?php
namespace App\Listener;

use Tk\EventDispatcher\SubscriberInterface;

/**
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class MailHandler implements SubscriberInterface
{

    public function onPreSend(\Tk\EventDispatcher\Event $event)
    {
        vd('MAIL PRE SEND');

    }


    public function onPostSend(\Tk\EventDispatcher\Event $event)
    {
        vd('MAIL PRE SEND');

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
            \Tk\Mail\MailEvents::PRE_SEND => 'onPreSend',
            \Tk\Mail\MailEvents::POST_SEND => 'onPostSend'
        );
    }
    
    
}