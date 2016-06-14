<?php
namespace App\Listener;


use Tk\EventDispatcher\SubscriberInterface;
use App\Event\AuthEvent;
use Tk\Kernel\KernelEvents;
use Tk\Event\ControllerEvent;


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
            $result = $event->getAuth()->authenticate($adapter);
            $event->setResult($result);
            if ($result && $result->getCode() == \Tk\Auth\Result::SUCCESS) {
                break;
            }
        }
        
        if ($result->isValid()) {
            /** @var \App\Db\User $user */
            $user = \App\Db\User::getMapper()->findByUsername($result->getIdentity());
            if (!$user) {
                throw new \Tk\Auth\Exception('User not found: Contact Your Administrator.');
            }
            $user->lastLogin = new \DateTime();
            $user->save();
            //$user->redirectHome();
            \Tk\Uri::create($user->getHomeUrl())->redirect();
        }
    }

    /**
     * @param AuthEvent $event
     * @throws \Exception
     */
    public function onLogout(AuthEvent $event)
    {
        $event->getAuth()->clearIdentity();
    }
    
    /**
     * Check the user has access to this controller
     *
     * @param ControllerEvent $event
     */
    public function onControllerAccess(ControllerEvent $event)
    {
        /** @var \App\Controller\Iface $controller */
        $controller = current($event->getController());
        $user = $controller->getUser();
        if ($controller instanceof \App\Controller\Iface) {
            // TODO: This would be a good place for an ACL or RBAC in the future
            $access = $event->getRequest()->getAttribute('access');

            // Check the user has access to the controller in question
            if (empty($access)) return;
            if (!$user) \Tk\Uri::create('/login.html')->redirect();
            if (!\App\Auth\Access::create($user)->hasRole($access)) {
                // Could redirect to a authentication error page...
                // Could cause a loop if the permissions are stuffed
                \App\Alert::getInstance()->addWarning('You do not have access to the requested page.');
                \Tk\Uri::create($user->getHomeUrl())->redirect();
            }
        }
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
            KernelEvents::CONTROLLER => 'onControllerAccess',
            'auth.onLogin' => 'onLogin',
            'auth.onLogout' => 'onLogout'
        );
    }
    
    
}