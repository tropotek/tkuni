<?php
namespace App\Listener;

use Tk\EventDispatcher\SubscriberInterface;
use App\Event\AuthEvent;
use Tk\Kernel\KernelEvents;
use Tk\Event\KernelEvent;
use Tk\Event\ControllerEvent;
use Tk\Event\GetResponseEvent;

/**
 * Class StartupHandler
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class AuthHandler implements SubscriberInterface
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
            $user = \App\Db\User::getMapper()->findByUsername($ident['username'], $ident['institutionId']);
            $config->setUser($user);
        }
    }

    /**
     * Check the user has access to this controller
     *
     * @param ControllerEvent $event
     */
    public function onControllerAccess(ControllerEvent $event)
    {
        /** @var \App\Controller\Iface $controller */
        $controller = $event->getController();
        $user = $controller->getUser();
        if ($controller instanceof \App\Controller\Iface) {

            // Get page access permission from route params (see config/routes.php)
            $role = $event->getRequest()->getAttribute('access');
            // Check the user has access to the controller in question
            if (!$role || empty($role)) return;

            if (!$user) \Tk\Uri::create('/login.html')->redirect();
            if (!$user->getAcl()->hasRole($role)) {
                // Could redirect to a authentication error page...
                // Could cause a loop if the permissions are stuffed
                \App\Alert::getInstance()->addWarning('You do not have access to the requested page.');
                \Tk\Uri::create($user->getHomeUrl())->redirect();
            }
        }
    }

    /**
     * @param AuthEvent $event
     * @throws \Exception
     */
    public function onLogin(AuthEvent $event)
    {
        $config = \App\Factory::getConfig();
        $result = null;
        $adapterList = $config->get('system.auth.adapters');

        foreach($adapterList as $name => $class) {
            $adapter = \App\Factory::getAuthAdapter($class, $event->all());
            if (!$adapter) continue;
            $result = $event->getAuth()->authenticate($adapter);
            $event->setResult($result);
            if ($result && $result->getCode() == \Tk\Auth\Result::SUCCESS) {
                break;
            }
        }
        if (!$result) {
            throw new \Tk\Auth\Exception('Invalid login credentials');
        }
        if (!$result->isValid()) {
            return;
        }

        /** @var \App\Db\User $user */
        $ident = $result->getIdentity();
        $user = \App\Db\UserMap::create()->findByUsername($ident['username'], $ident['institutionId']);
        if (!$user) {
            throw new \Tk\Auth\Exception('User not found: Contact Your Administrator.');
        }

        $event->set('user', $user);
    }

    /**
     * @param AuthEvent $event
     * @throws \Exception
     */
    public function onLoginSuccess(AuthEvent $event)
    {

        /** @var \App\Db\User $user */
        $user = $event->get('user');
        if (!$user) {
            throw new \Tk\Exception('No user found.');
        }

        $user->lastLogin = \Tk\Date::create();
        $user->save();

        $institution = $user->getInstitution();
        if ($institution && ($user->hasRole(\App\Auth\Acl::ROLE_STUDENT) || $user->hasRole(\App\Auth\Acl::ROLE_STAFF)) ) {
            $courseList = \App\Db\CourseMap::create()->findPendingEnrollment($institution->id, $user->email);
            /** @var \App\Db\Course $course */
            foreach ($courseList as $course) {
                \App\Db\CourseMap::create()->addUser($course->id, $user->id);
            }
        }

        \Tk\Uri::create($user->getHomeUrl())->redirect();

    }

    /**
     * @param AuthEvent $event
     * @throws \Exception
     */
    public function onLogout(AuthEvent $event)
    {
        $event->getAuth()->clearIdentity();

        // check if we are in an lti session then return to the LMS

    }



    public function onRegister(\Tk\EventDispatcher\Event $event)
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
        $message->send();

    }

    public function onRegisterConfirm(\Tk\EventDispatcher\Event $event)
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
        $message->send();

    }

    public function onRecover(\Tk\EventDispatcher\Event $event)
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
        $message->send();

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
            \App\Auth\AuthEvents::LOGIN => 'onLogin',
            \App\Auth\AuthEvents::LOGIN_SUCCESS => 'onLoginSuccess',
            \App\Auth\AuthEvents::LOGOUT => 'onLogout',
            \App\Auth\AuthEvents::REGISTER => 'onRegister',
            \App\Auth\AuthEvents::REGISTER_CONFIRM => 'onRegisterConfirm',
            \App\Auth\AuthEvents::RECOVER => 'onRecover'
        );
    }
    
    
}