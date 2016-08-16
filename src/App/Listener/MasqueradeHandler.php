<?php
namespace App\Listener;


use Tk\EventDispatcher\SubscriberInterface;
use Tk\Kernel\KernelEvents;
use Tk\Event\GetResponseEvent;
use Tk\Request;


/**
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class MasqueradeHandler implements SubscriberInterface
{

    /**
     * constructor.
     *
     */
    public function __construct()
    {

    }

    /**
     * Add any headers to the final response.
     * 
     * @param GetResponseEvent $event
     */
    public function onMasquerade(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->has('msq')) return;
        $msqUser = \App\Db\UserMap::create()->find($request->get('msq'));
        if (!$msqUser) {
            throw new \Tk\Exception('Cannot masquerade as this user.');
        }
        // TODO: Check if already masquerading, disalow nested masquerading for now.

        $user = \App\Factory::getConfig()->getUser();







    }

    /**
     * @param \App\Db\User $user
     * @param \App\Db\User $msqUser
     * @return bool
     */
    protected function canMasquerade($user, $msqUser)
    {
        $inst = $user->getInstitution();
        $mInst = $msqUser->getInstitution();

        switch($user->role) {
            case \App\Auth\Acl::ROLE_ADMIN:
                return true;
            case \App\Auth\Acl::ROLE_CLIENT:
                //if ($msqUser->hasRole())
                return false;
            case \App\Auth\Acl::ROLE_STAFF:
                return false;
        }
        return false;
    }

    /**
     * getSubscribedEvents
     * 
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'onMasquerade'
        );
    }
}