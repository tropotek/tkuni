<?php
namespace App\Listener;

use Tk\Event\Subscriber;
use Symfony\Component\HttpKernel\KernelEvents;
use Tk\Event\AuthEvent;
use Tk\Auth\AuthEvents;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class SubjectHandler implements Subscriber
{

    /**
     * If we are in a subject URL then get the subject object and set it in the config
     * for global accessibility.
     *
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     * @throws \Exception
     */
    public function onRequest(\Symfony\Component\HttpKernel\Event\RequestEvent $event)
    {
        $config = \App\Config::getInstance();
        $request = $event->getRequest();

        if ($config->getUser()) {
            \Tk\Log::info('- User: ' . $config->getUser()->getName() . ' <' . $config->getUser()->getEmail() . '> [ID: ' . $config->getUser()->getId() . ']');
            if ($config->getMasqueradeHandler()->isMasquerading()) {
                $msq = $config->getMasqueradeHandler()->getMasqueradingUser();
                \Tk\Log::info('  └ Msq: ' . $msq->getName() . ' [ID: ' . $msq->getId() . ']');
            }
        }
        if ($config->getInstitution()) {
            \Tk\Log::info('- Institution: ' . $config->getInstitution()->getName() . ' [ID: ' . $config->getInstitution()->getId() . ']');
        }
        if ($request->hasAttribute('subjectCode') && $config->getSubject()) {
            \Tk\Log::info('- Subject: ' . $config->getSubject()->name . ' [ID: ' . $config->getSubject()->getId() . ']');
        }

    }

    /**
     * Ensure this is run after App\Listener\CrumbsHandler::onFinishRequest()
     *
     * @param AuthEvent $event
     * @throws \Exception
     */
    public function onLoginSuccess(AuthEvent $event)
    {
        $result = $event->getResult();
        /* @var \Uni\Db\User $user */
        $user = \Uni\Db\UserMap::create()->find($result->getIdentity());
        $institution = $user->getInstitution();

        // Enroll to any pending subjects
        if ($institution && $user->getRole()->hasType(array(\Uni\Db\Role::TYPE_STUDENT, \Uni\Db\Role::TYPE_COORDINATOR)) ) {
            // Get any alias email addresses
            $ldapData = $user->getData()->get('ldap.data');
            $alias = array();
            if ($ldapData && !empty($ldapData['mailalternateaddress'][0])) {
                $alias[] = $ldapData['mailalternateaddress'][0];
            }
            $emailList = array_merge(array($user->email), $alias);
            foreach ($emailList as $i => $email) {
                $subjectList = \Uni\Db\SubjectMap::create()->findPendingPreEnrollments($institution->getId(), $email);
                /* @var \Uni\Db\Subject $subject */
                foreach ($subjectList as $subject) {
                    \Uni\Db\SubjectMap::create()->addUser($subject->getId(), $user->getId());
                }
            }
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
            AuthEvents::LOGIN_SUCCESS => 'onLoginSuccess',
            KernelEvents::REQUEST => array('onRequest', -1)
        );
    }
}

