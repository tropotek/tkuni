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
     */
    public function onSystemInit(GetResponseEvent $event)
    {
        // if a user is in the session add them to the global config
        // Only the identity details should be in the auth session not the full user object, to save space and be secure.
        $config = \App\Factory::getConfig();
        $auth = \App\Factory::getAuth();
        if ($auth->getIdentity()) {
            $ident = $auth->getIdentity();
            $user = \App\Db\UserMap::create()->find($ident);
            if ($user)
                $config->setUser($user);
        }
    }

    /**
     * Check the user has access to this controller
     *
     * @param ControllerEvent $event
     * @throws \Tk\Auth\Exception
     * @throws \Exception
     */
    public function onControllerAccess(ControllerEvent $event)
    {
        /** @var \App\Controller\Iface $controller */
        $controller = $event->getController();
        /** @var \App\Db\User $user */
        $user = \App\Factory::getConfig()->getUser();
        $role = $event->getRequest()->getAttribute('role');
        if (!$role || empty($role)) return;
        if (!$user) {
            if ($controller instanceof \App\Controller\Iface) {
                \Tk\Uri::create('/login.html')->redirect();
            } else {
                throw new \Tk\Auth\Exception('Invalid access permissions');
            }
        } else {
            if ($user->sessionId != \App\Factory::getSession()->getId()) {
                $user->sessionId = \App\Factory::getSession()->getId();
                $user->save();
            }
            if (!$user->hasRole($role) && $user->active) {
                \Tk\Alert::addWarning('You do not have access to the requested page.');
                $user->getHomeUrl()->redirect();
            }
        }
    }

    /**
     * @param AuthEvent $event
     * @throws \Exception
     */
    public function onLogin(AuthEvent $event)
    {
        $adapter = \App\Factory::getAuthDbTableAdapter($event->all());
        $result = $event->getAuth()->authenticate($adapter);

        $event->setResult($result);
        $event->set('auth.password.access', true);   // Can modify their own password

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

        //vd($user, $event->getRedirect());
        if($user && $event->getRedirect() == null) {
            $event->setRedirect($user->getHomeUrl());
        }

        //store the type of adapter for allowing the staff student to modify their password
        \App\Factory::getSession()->set('auth.password.access', ($event->get('auth.password.access') === true) );

        // Update the user record.
        $user->lastLogin = \Tk\Date::create();
        $user->save();
    }

    /**
     * @param AuthEvent $event
     * @throws \Exception
     */
    public function onLogout(AuthEvent $event)
    {
        /** @var \App\Db\User $user */
        $user = \Tk\Config::getInstance()->getUser();
        if ($user) {
            if (!$event->getRedirect()) {
                $event->setRedirect(\Tk\Uri::create('/index.html'));
                if ($user->getInstitution()) {
                    $event->setRedirect(\Tk\Uri::create('/inst/' . $user->getInstitution()->getHash() . '/login.html'));
                }
            }
            $user->sessionId = '';
            $user->save();
            $auth = $event->getAuth();
            $auth->clearIdentity();
            \App\Factory::getSession()->destroy();
        }

    }

    /**
     * @param \Tk\Event\Event $event
     * @throws \Dom\Exception
     * @throws \Tk\Exception
     * @throws \Tk\Mail\Exception
     */
    public function onRegister(\Tk\Event\Event $event)
    {
        /** @var \App\Db\User $user */
        $user = $event->get('user');

        // on success email user confirmation
        $body = \Dom\Loader::loadFile($event->get('templatePath').'/xtpl/mail/account.registration.xtpl');
        $body->insertText('name', $user->name);
        $url = \Tk\Uri::create('/register.html')->set('h', $user->hash);
        $body->insertText('url', $url->toString());
        $body->setAttr('url', 'href', $url->toString());
        $subject = 'Account Registration Request.';

        $message = new \Tk\Mail\Message($body->toString(), $subject, $user->email, \App\Factory::getConfig()->get('site.email'));
        \App\Factory::getEmailGateway()->send($message);
    }

    /**
     * @param \Tk\Event\Event $event
     * @throws \Dom\Exception
     * @throws \Tk\Exception
     * @throws \Tk\Mail\Exception
     */
    public function onRegisterConfirm(\Tk\Event\Event $event)
    {
        /** @var \App\Db\User $user */
        $user = $event->get('user');

        // Send an email to confirm account active
        $body = \Dom\Loader::loadFile($event->get('templatePath').'/xtpl/mail/account.activated.xtpl');
        $body->insertText('name', $user->name);
        $url = \Tk\Uri::create('/login.html');
        $body->insertText('url', $url->toString());
        $body->setAttr('url', 'href', $url->toString());
        $subject = 'Account Registration Activation.';

        $message = new \Tk\Mail\Message($body->toString(), $subject, $user->email, \App\Factory::getConfig()->get('site.email'));
        \App\Factory::getEmailGateway()->send($message);
    }

    /**
     * @param \Tk\Event\Event $event
     * @throws \Dom\Exception
     * @throws \Tk\Exception
     * @throws \Tk\Mail\Exception
     */
    public function onRecover(\Tk\Event\Event $event)
    {
        /** @var \App\Db\User $user */
        $user = $event->get('user');
        $pass = $event->get('password');

        // Send an email to confirm account active
        $body = \Dom\Loader::loadFile($event->get('templatePath').'/xtpl/mail/account.recover.xtpl');
        $body->insertText('name', $user->name);
        $body->insertText('password', $pass);
        $url = \Tk\Uri::create('/login.html');
        $body->insertText('url', $url->toString());
        $body->setAttr('url', 'href', $url->toString());
        $subject = 'Account Password Recovery.';

        $message = new \Tk\Mail\Message($body->toString(), $subject, $user->email, \App\Factory::getConfig()->get('site.email'));
        \App\Factory::getEmailGateway()->send($message);
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
            KernelEvents::REQUEST => 'onSystemInit',
            KernelEvents::CONTROLLER => 'onControllerAccess',
            AuthEvents::LOGIN => 'onLogin',
            AuthEvents::LOGIN_SUCCESS => 'onLoginSuccess',
            AuthEvents::LOGOUT => 'onLogout',
            AuthEvents::REGISTER => 'onRegister',
            AuthEvents::REGISTER_CONFIRM => 'onRegisterConfirm',
            AuthEvents::RECOVER => 'onRecover'
        );
    }

}