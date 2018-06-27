<?php
namespace App\Listener;

use Tk\Event\Subscriber;
use Tk\Kernel\KernelEvents;
use Tk\Event\ControllerEvent;
use Tk\Event\GetResponseEvent;
use Tk\Event\AuthEvent;
use Tk\Auth\AuthEvents;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class AuthHandler implements Subscriber
{

    /**
     * do any auth init setup
     *
     * @param GetResponseEvent $event
     * @throws \Tk\Db\Exception
     * @throws \Tk\Exception
     */
    public function onRequest(GetResponseEvent $event)
    {
        // if a user is in the session add them to the global config
        // Only the identity details should be in the auth session not the full user object, to save space and be secure.
        $config = \App\Config::getInstance();
        $auth = $config->getAuth();
        /** @var \App\Db\User $user */
        $user = \App\Db\User::getMapper()->find($auth->getIdentity());
        //if (!$user) $user = new \App\Db\User();     // public user
        $config->setUser($user);

        $role = $event->getRequest()->getAttribute('role');
        if (!$role || empty($role)) return;

        if (!$user || $user->hasRole(\App\Db\User::ROLE_PUBLIC)) {
            if ($event->getRequest()->getUri()->getRelativePath() != '/login.html') {
                \Tk\Uri::create('/login.html')->redirect();
            } else {
                throw new \Tk\Auth\Exception('Invalid access permissions');
            }
        }

        if (!$user->hasRole($role)) {
            \Tk\Alert::addWarning('You do not have access to the requested page.');
            $user->getHomeUrl()->redirect();
        }
    }

    /**
     * @param AuthEvent $event
     * @throws \Exception
     */
    public function onLogin(AuthEvent $event)
    {
        $config = \App\Config::getInstance();
        $auth = $config->getAuth();

        $adapter = $config->getAuthDbTableAdapter($event->all());
        $result = $auth->authenticate($adapter);

        $event->setResult($result);
        $event->set('auth.password.access', true);   // Can modify their own password
    }

    /**
     * @param \Tk\Event\AuthAdapterEvent $event
     * @return null|void
     * @throws \Tk\Db\Exception
     * @throws \Tk\Exception
     */
    public function onLoginProcess(\Tk\Event\AuthAdapterEvent $event)
    {
        if ($event->getAdapter() instanceof \Tk\Auth\Adapter\Ldap) {
            /** @var \Tk\Auth\Adapter\Ldap $adapter */
            $adapter = $event->getAdapter();
            $config = \App\Config::getInstance();

            // Find user data from ldap connection
            $filter = substr($adapter->getBaseDn(), 0, strpos($adapter->getBaseDn(), ','));
            if ($filter) {
                $sr = @ldap_search($adapter->getLdap(), $adapter->getBaseDn(), $filter);
                $ldapData = @ldap_get_entries($adapter->getLdap(), $sr);
                if ($ldapData) {
                    // Use this info to create an LDAP user for their first login or to update their details
                    /* @var \App\Db\User $user */
                    $user = \App\Db\UserMap::create()->findByUsername($adapter->get('username'), $config->getInstitutionId());
//                    if (!$user) {
//                        // Create user???
//                    }
                    if ($user) {
                        if (!$user->uid && !empty($ldapData[0]['auedupersonid'][0]))
                            $user->uid = $ldapData[0]['auedupersonid'][0];
                        if (!$user->email && !empty($ldapData[0]['mail'][0]))
                            $user->email = $ldapData[0]['mail'][0];
                        if (!$user->name && !empty($ldapData[0]['displayname'][0]))
                            $user->name = $ldapData[0]['displayname'][0];
                        $user->setNewPassword($adapter->get('password'));
                        $user->save();
                        if (method_exists($user, 'getData')) {
                            $data = $user->getData();
                            $data->set('ldap.last.login', json_encode($ldapData));
                            if (!empty($ldapData[0]['ou'][0]))
                            $data->set('faculty', $ldapData[0]['ou'][0]);
                            $data->save();
                        }
                        $event->setResult(new \Tk\Auth\Result(\Tk\Auth\Result::SUCCESS, $user->getId(), 'User Found!'));
                    }
                }
            }
        }
    }

    /**
     * @param AuthEvent $event
     * @throws \Exception
     */
    public function onLoginSuccess(AuthEvent $event)
    {
        $result = $event->getResult();
        if (!$result) {
            throw new \Tk\Auth\Exception('Invalid login credentials');
        }
        if (!$result->isValid()) {
            return;
        }

        /* @var \App\Db\User $user */
        $user = \App\Db\UserMap::create()->find($result->getIdentity());
        if (!$user) {
            throw new \Tk\Auth\Exception('Invalid user login credentials');
        }
        if (!$user->active) {
            throw new \Tk\Auth\Exception('Inactive account, please contact your administrator.');
        }

        if($user && $event->getRedirect() == null) {
            $event->setRedirect($user->getHomeUrl());
        }

        //store the type of adapter for allowing the staff student to modify their password
        \App\Config::getInstance()->getSession()->set('auth.password.access', ($event->get('auth.password.access') === true) );

        // Update the user record.
        if ($user->sessionId != \App\Config::getInstance()->getSession()->getId()) {
            $user->sessionId = \App\Config::getInstance()->getSession()->getId();
        }
        $user->lastLogin = \Tk\Date::create();
        $user->save();
    }

    /**
     * @param AuthEvent $event
     * @throws \Exception
     */
    public function onLogout(AuthEvent $event)
    {
        $config = \App\Config::getInstance();
        $auth = $config->getAuth();
        /** @var \App\Db\User $user */
        $user = $config->getUser();

        if (!$event->getRedirect()) {
            $url = \Tk\Uri::create('/index.html');
            if ($user && !$user->isClient() && !$user->isAdmin() && $user->getInstitution()) {
                $url = \Uni\Uri::createInstitutionUrl('/login.html', $user->getInstitution());
            }
            $event->setRedirect($url);
        }

        if ($user) {
            $user->sessionId = '';
            $user->save();
        }

        $auth->clearIdentity();
        $config->getSession()->destroy();
    }

    /**
     * @param \Tk\Event\Event $event
     * @throws \Exception
     */
    public function onRegister(\Tk\Event\Event $event)
    {
        /** @var \App\Db\User $user */
        $user = $event->get('user');
        $config = \App\Config::getInstance();

        $url = \Tk\Uri::create('/register.html')->set('h', $user->hash);

        $message = $config->createMessage('account.activated');
        $message->setSubject('Account Registration.');
        $message->addTo($user->email);
        $message->set('name', $user->name);
        $message->set('activate-url', $url->toString());
        \App\Config::getInstance()->getEmailGateway()->send($message);
    }

    /**
     * @param \Tk\Event\Event $event
     * @throws \Exception
     */
    public function onRegisterConfirm(\Tk\Event\Event $event)
    {
        /** @var \App\Db\User $user */
        $user = $event->get('user');
        $config = \App\Config::getInstance();

        // Send an email to confirm account active
        $url = \Tk\Uri::create('/login.html');

        $message = $config->createMessage('account.activated');
        $message->setSubject('Account Activation.');
        $message->addTo($user->email);
        $message->set('name', $user->name);
        $message->set('login-url', $url->toString());
        \App\Config::getInstance()->getEmailGateway()->send($message);
    }

    /**
     * @param \Tk\Event\Event $event
     * @throws \Exception
     */
    public function onRecover(\Tk\Event\Event $event)
    {
        /** @var \App\Db\User $user */
        $user = $event->get('user');
        $pass = $event->get('password');
        $config = \App\Config::getInstance();

        $url = \Tk\Uri::create('/login.html');

        $message = $config->createMessage('account.activated');
        $message->setSubject('Password Recovery');
        $message->addTo($user->email);
        $message->set('name', $user->name);
        $message->set('password', $pass);
        $message->set('login-url', $url->toString());
        \App\Config::getInstance()->getEmailGateway()->send($message);
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
            KernelEvents::REQUEST => 'onRequest',
            AuthEvents::LOGIN => 'onLogin',
            AuthEvents::LOGIN_PROCESS => 'onLoginProcess',
            AuthEvents::LOGIN_SUCCESS => 'onLoginSuccess',
            AuthEvents::LOGOUT => 'onLogout',
            AuthEvents::REGISTER => 'onRegister',
            AuthEvents::REGISTER_CONFIRM => 'onRegisterConfirm',
            AuthEvents::RECOVER => 'onRecover'
        );
    }

}