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
                \Tk\Alert::addWarning('You do not have access to the requested page.');
                $user->getHomeUrl()->redirect();
            }
        } else {
            if (!$user->hasRole($role)) {
                \Tk\Alert::addWarning('You do not have access to the requested page.');
                $user->getHomeUrl()->redirect();
            }
            if ($user->sessionId != $config->getSession()->getId()) {
                $user->sessionId = $config->getSession()->getId();
                $user->save();
            }
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
     * @param \Tk\Event\AuthEvent $event
     * @return null|void
     * @throws \ReflectionException
     * @throws \Tk\Db\Exception
     * @throws \Tk\Exception
     */
    public function onLoginProcess(\Tk\Event\AuthEvent $event)
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
                    $email = $ldapData[0]['mail'][0];   // Email format = firstname.lastname@unimelb

                    // Use this info to create an LDAP user for their first login or to update their details
                    /* @var \App\Db\User $user */
                    $user = \App\Db\UserMap::create()->findByUsername($adapter->get('username'), $config->getInstitutionId());
//                    if (!$user) { // Create a user record if none exists
//                        $role = 'student';
//                        if (preg_match('/(staff|student)/', strtolower($ldapData[0]['auedupersontype'][0]), $reg)) {
//                            if ($reg[1] == 'staff') {
//                                $role = 'staff';
//                            } else if ($reg[1] == 'student') {
//                                $role = 'student';
//                            }
//                        }
//                        if ($role == 'student') {
//                            // To check if a user is pre-enrolled get an array of uid and emails for a user
//                            $isPreEnrolled = \App\Db\Subject::isPreEnrolled($config->getInstitutionId(),
//                                array_merge($ldapData[0]['mail'], $ldapData[0]['mailalternateaddress']),
//                                $ldapData[0]['auedupersonid'][0]
//                            );
//                            if (!$isPreEnrolled) return;  // Only create users accounts for enrolled students
//
//                            $userData = array(
//                                'type' => 'ldap',
//                                'institutionId' => $config->getInstitutionId(),
//                                'username' => $event->get('username'),
//                                'userGroupId' => $role,
//                                'active' => true,
//                                'email' => $email,
//                                'name' => $ldapData[0]['displayname'][0],
//                                'uid' => $ldapData[0]['auedupersonid'][0],
//                                'ldapData' => $ldapData
//                            );
//                            $user = new \App\Db\User();
//                            \App\Db\UserMap::create()->mapForm($userData, $user);
//                            $user->setNewPassword($event->get('password'));
//                            $user->save();
//
//                            // Save the last ldap data for reference
//                            $user->getData()->set('ldap.data', json_encode($ldapData, \JSON_PRETTY_PRINT));
//                            $user->getData()->save();
//                        }
//                    }
                    if ($user && $user->active) {
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
                        $event->setResult(new \Tk\Auth\Result(\Tk\Auth\Result::SUCCESS, $user->getId()));
                    }
                }
            }
        }


        // TODO: This may need further work, getting a nested session save issue..
        // There is an issue here with going from LDAP and LTI
        //  LTI we only have their name email, however with LDAP the email is their username one GGGRRRR!!
        // EG:
        //  LTI: michael.mifsud@unimelb...
        //  LDAP: mifsudm@unimelb....

        if ($event->getAdapter() instanceof \Lti\Auth\LtiAdapter) {
            /** @var \Lti\Auth\LtiAdapter $adapter */
            $adapter = $event->getAdapter();
            $userData = $adapter->get('userData');

            $user = \App\Db\UserMap::create()->findByEmail($userData['email'], $adapter->getInstitution()->getId());
//            if (!$user) {   // Find user by username (this is the start pat of the email address, not reliable
//                $user = \App\Db\UserMap::create()->findByUsername($userData['username'], $adapter->getInstitution()->getId());
//            }

//            if (!$user) {   // Create the new user account
//                // optional to check the pre-enrollment list before creation
//                $isPreEnrolled = \App\Db\Subject::isPreEnrolled($adapter->getInstitution()->getId(), array($userData['email']) );
//                if (!$isPreEnrolled) {  // Only create users accounts for enrolled students
//                    return;
//                }
//                $user = new \App\Db\User();
//                \App\Db\UserMap::create()->mapForm($userData, $user);
//                $user->save();
//                $adapter->setUser($user);
//            }
            if (!$user) {
                return;
            }
            $subjectData = $adapter->get('subjectData');
            $subject = \App\Db\SubjectMap::create()->find($subjectData['id']);
            if (!$subject) {
                $subject = \App\Db\SubjectMap::create()->findByCode($subjectData['code'], $adapter->getInstitution()->getId());
            }
            if (!$subject) {
                throw new \Tk\Exception('Subject not available, Please contact subject coordinator.');
                // Create a new subject here if needed ????
            }
            $config->getSession()->set('lti.subjectId', $subject->getId());   // Limit the dashboard to one subject for LTI logins
            $config->getSession()->set('auth.password.access', false);
            $event->setResult(new \Tk\Auth\Result(\Tk\Auth\Result::SUCCESS, $user->getId()));
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

        if ($user && $user->getRole() != \App\Db\User::ROLE_PUBLIC) {
            $user->sessionId = '';
            $user->save();
        }
        $config->getSession()->remove('lti.subjectId'); // Remove limit the dashboard to one subject for LTI logins
        $config->getSession()->remove('auth.password.access');
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