<?php
namespace App\Listener;

use Tk\Event\Subscriber;
use Tk\Kernel\KernelEvents;
use Tk\Event\GetResponseEvent;


/**
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class InstitutionHandler implements Subscriber
{


    /**
     * Set the global institution into the config as a central data access point
     * If no institution is set then we know we are either an admin or public user...
     *
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        /** @var \App\Db\User $user */
        $user = \Tk\Config::getInstance()->getUser();
        if ($user && $user->getInstitution()) {
            \Tk\Config::getInstance()->setInstitution($user->getInstitution());
        }
        if ($event->getRequest()->getAttribute('instHash')) {
            $institution = \App\Db\InstitutionMap::create()->findByHash($event->getRequest()->getAttribute('instHash'));
            \Tk\Config::getInstance()->setInstitution($institution);
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
            KernelEvents::REQUEST => array('onRequest', -1)
        );
    }
}


