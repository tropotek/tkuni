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
     * @throws \Tk\Exception
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
        if (!$this->canMasquerade($user, $msqUser)) {
            throw new \Tk\Exception('Cannot masquerade as this user.');
        }

        vd('TODO: Implement Masquerading...');
        
    }

    /**
     * @param \App\Db\User $user
     * @param \App\Db\User $msqUser
     * @return bool
     */
    protected function canMasquerade($user, $msqUser)
    {
        switch($user->role) {
            case \App\Auth\Acl::ROLE_ADMIN:
                return true;
            case \App\Auth\Acl::ROLE_CLIENT:
                $inst = $user->getInstitution();
                $mInst = $msqUser->getInstitution();
                if (!$msqUser->hasRole(\App\Auth\Acl::ROLE_ADMIN) && $inst->id == $mInst->id)
                    return true;
                return false;
            case \App\Auth\Acl::ROLE_STAFF:
                if ($msqUser->hasRole(\App\Auth\Acl::ROLE_STUDENT) && $user->institutionId == $msqUser->institutionId)
                    return true;
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