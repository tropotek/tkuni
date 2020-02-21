<?php
namespace App\Listener;

use Tk\ConfigTrait;
use Tk\Event\Subscriber;
use Tk\Event\AuthEvent;
use Tk\Auth\AuthEvents;
use Symfony\Component\HttpKernel\KernelEvents;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class SubjectHandler implements Subscriber
{
    use ConfigTrait;

    /**
     * If we are in a subject URL then get the subject object and set it in the config
     * for global accessibility.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     * @throws \Exception
     */
    public function onRequest( $event)
    {
        $config = $this->getConfig();
        $request = $event->getRequest();

        if ($config->getAuthUser()) {
            \Tk\Log::info('- User: ' . $config->getAuthUser()->getName() . ' <' . $config->getAuthUser()->getEmail() . '> [ID: ' . $config->getAuthUser()->getId() . ']');
            if ($config->getMasqueradeHandler()->isMasquerading()) {
                $msq = $config->getMasqueradeHandler()->getMasqueradingUser();
                \Tk\Log::info('  └ Msq: ' . $msq->getName() . ' [ID: ' . $msq->getId() . ']');
            }
        }
        if ($config->getInstitution()) {
            \Tk\Log::info('- Institution: ' . $config->getInstitution()->getName() . ' [ID: ' . $config->getInstitution()->getId() . ']');
        }
        if ($request->attributes->has('subjectCode') && $config->getSubject()) {
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
        $user = $this->getConfig()->getUserMapper()->find($result->getIdentity());
        $institution = $user->getInstitution();

        // Enroll to any pending subjects
        if ($institution && $user->hasType(array(\Uni\Db\User::TYPE_STUDENT, \Uni\Db\User::TYPE_STAFF)) ) {
            // Get any alias email addresses
            $ldapData = $user->getData()->get('ldap.data');
            $alias = array();
            if ($ldapData && !empty($ldapData['mailalternateaddress'][0])) {
                $alias[] = $ldapData['mailalternateaddress'][0];
            }
            $emailList = array_merge(array($user->getEmail()), $alias);
            foreach ($emailList as $i => $email) {
                $subjectList = $this->getConfig()->getSubjectMapper()->findPendingPreEnrollments($institution->getId(), $email);
                /* @var \Uni\Db\Subject $subject */
                foreach ($subjectList as $subject) {
                    if ($user->isStaff()) {
                        $this->getConfig()->getCourseMapper()->addUser($subject->getId(), $user->getId());
                    } else if ($user->isStudent()) {
                        //$this->getConfig()->getSubjectMapper()->addUser($subject->getId(), $user->getId());
                        if ($user->isStudent())
                            $this->getConfig()->getSubjectMapper()->addUser($subject->getId(), $user->getId());
                        if ($user->isStaff())
                            $this->getConfig()->getCourseMapper()->addUser($subject->getCourseId(), $user->getId());
                    }

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

