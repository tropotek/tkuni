<?php
namespace App\Listener;

use Tk\EventDispatcher\SubscriberInterface;
use Tk\Kernel\KernelEvents;
use Tk\Event\GetResponseEvent;
use App\Db\User;
use Tk\Event\AuthEvent;
use Tk\Auth\AuthEvents;

/**
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class MasqueradeHandler implements SubscriberInterface
{
    /**
     * Session ID
     */
    const SID = '__masquerade__';

    /**
     * The query string for the msq user
     * Eg: `index.html?msq=23`
     */
    const MSQ = 'msq';

    /**
     * Add any headers to the final response.
     *
     * @param GetResponseEvent $event
     * @throws \Tk\Exception
     */
    public function onMasquerade(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->has(self::MSQ)) return;

        try {
            /** @var User $user */
            $user = \App\Factory::getConfig()->getUser();
            if (!$user) throw new \Tk\Exception('Invalid User');
            /** @var User $msqUser */
            $msqUser = \App\Db\UserMap::create()->findByhash($request->get(self::MSQ));
            if (!$msqUser) throw new \Tk\Exception('Invalid User');
            self::masqueradeLogin($user, $msqUser);
        } catch (\Exception $e) {
            \Tk\Alert::addWarning($e->getMessage());
        }
    }



    // -------------------  Masquerade functions  -------------------

    /**
     * Check if this user can masquerade as the supplied msqUser
     *
     * @param User $user
     * @param User $msqUser
     * @return bool
     */
    public static function canMasqueradeAs($user, $msqUser)
    {
        if (!$msqUser || !$user) return false;
        if ($user->id == $msqUser->id) return false;

        $msqArr = \App\Factory::getSession()->get(self::SID);
        if (is_array($msqArr)) {    // Check if we are allready masquerading as this user in the queue
            foreach ($msqArr as $data) {
                if ($data['userId'] == $msqUser->id) return false;
            }
        }

        // Get the users role precedence order index
        $userRoleIdx = array_search($user->role, \App\Auth\Acl::$roleOrder);
        $msqRoleIdx = array_search($msqUser->role, \App\Auth\Acl::$roleOrder);

        // If not admin their role must be higher in precedence see \App\Db\User::$roleOrder
        if (!$user->hasRole(\App\Auth\Acl::ROLE_ADMIN) && $userRoleIdx >= $msqRoleIdx) {
            return false;
        }

        // If not admins they must be of the same institution
        if (!$user->hasRole(\App\Auth\Acl::ROLE_ADMIN) && $user->getInstitution()->id != $msqUser->institutionId) {
            return false;
        }
        return true;
    }

    /**
     * If this user is masquerading
     *
     * 0 if not masquerading
     * >0 The masquerading total (for nested masquerading)
     *
     * @return int
     */
    public static function isMasquerading()
    {
        if (!\App\Factory::getSession()->has(self::SID)) return 0;
        $msqArr = \App\Factory::getSession()->get(self::SID);
        return count($msqArr);
    }

    /**
     *
     * @param User $user
     * @param User $msqUser
     * @return bool|void
     * @throws \Tk\Exception
     */
    public static function masqueradeLogin($user, $msqUser)
    {
        if (!$msqUser || !$user) return;
        if ($user->id == $msqUser->id) return;

        // Get the masquerade queue from the session
        $msqArr = \App\Factory::getSession()->get(self::SID);
        if (!is_array($msqArr)) $msqArr = array();

        if (!self::canMasqueradeAs($user, $msqUser)) {
            return;
        }

        // Save the current user and url to the session, to allow logout
        $userData = array(
            'userId' => $user->id,
            'url' => \Tk\Uri::create()->remove(self::MSQ)->toString()
        );
        array_push($msqArr, $userData);
        // Save the updated masquerade queue
        \App\Factory::getSession()->set(self::SID, $msqArr);

        // Login as the selected user
        \App\Factory::getAuth()->getStorage()->write($msqUser->id);
        \Tk\Uri::create($msqUser->getHomeUrl())->redirect();
    }

    /**
     * masqueradeLogout
     *
     */
    public static function masqueradeLogout()
    {
        if (!self::isMasquerading()) return;
        if (!\App\Factory::getAuth()->hasIdentity()) return;
        $msqArr = \App\Factory::getSession()->get(self::SID);
        if (!is_array($msqArr) || !count($msqArr)) return;

        $userData = array_pop($msqArr);
        if (empty($userData['userId']) || empty($userData['url']))
            throw new \Tk\Exception('Session data corrupt. Clear session data and try again.');
        $userId = (int)$userData['userId'];
        $url = \Tk\Uri::create($userData['url']);

        // Save the updated masquerade queue
        \App\Factory::getSession()->set(self::SID, $msqArr);

        \App\Factory::getAuth()->getStorage()->write($userId);
        $url->redirect();
    }

    /**
     * @param AuthEvent $event
     * @throws \Exception
     */
    public function onLogout(AuthEvent $event)
    {
        if (self::isMasquerading()) {   // stop masquerading
            self::masqueradeLogout();
        }
    }


    /**
     * masqueradeLogout
     *
     */
    public static function masqueradeClear()
    {
        \App\Factory::getSession()->remove(self::SID);
    }



    /**
     * getSubscribedEvents
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'onMasquerade',
            AuthEvents::LOGOUT => array('onLogout', 10)
        );
    }
}