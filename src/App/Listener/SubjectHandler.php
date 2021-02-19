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
            AuthEvents::LOGIN_SUCCESS => 'onLoginSuccess'
        );
    }
}

