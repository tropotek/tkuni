<?php
namespace App;


/**
 * Plugin API factory interface
 *
 * This object should be sent to plugins so there is an interface
 * between the plugin and the app
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class PluginApi implements \Uni\PluginApi
{


    /**
     * @param $username
     * @param $institutionId
     * @return null|Db\User|\Tk\Db\Map\Model
     */
    public function findUser($username, $institutionId)
    {
        $user = null;
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $user = \App\Db\UserMap::create()->findByEmail($username, $institutionId);
        } else {
            $user = \App\Db\UserMap::create()->findByUsername($username, $institutionId);
        }
        return $user;
    }

    /**
     * @param array $params
     * @return null|Db\User
     * @throws \Tk\Exception
     */
    public function createUser($params = array())
    {
        $user = null;
        switch($params['type']) {
            case 'ldap':
            case 'lti':
                $user = \App\Config::createNewUser($params['institutionId'],
                    $params['username'], $params['email'], $params['role'], $params['password'], $params['name'], $params['uid'], $params['active']);
        }

        return $user;
    }

    /**
     * @param $subjectId
     * @return null|\Tk\Db\Map\Model|\app\Db\Subject
     */
    public function findSubject($subjectId)
    {
        return \App\Db\SubjectMap::create()->find($subjectId);
    }

    /**
     * @param $subjectCode
     * @param $institutionId
     * @return null|Db\Subject|\Tk\Db\ModelInterface
     */
    public function findSubjectByCode($subjectCode, $institutionId)
    {
        return \App\Db\SubjectMap::create()->findByCode($subjectCode, $institutionId);
    }

    /**
     * @param $params
     * @return Db\Subject|null
     */
    public function createSubject($params)
    {
        $subject = null;
        switch($params['type']) {
            case 'lti':
            case 'ldap':
                $subject = new \App\Db\Subject();
                \App\Db\SubjectMap::create()->mapForm($params, $subject);
                $subject->save();
                $this->addUserToSubject($subject, $params['UserIface']);
        }
        return $subject;
    }

    /**
     * @param \App\Db\Subject $subject
     * @param \app\Db\User $user
     */
    public function addUserToSubject($subject, $user)
    {
        \App\Db\SubjectMap::create()->addUser($subject->getId(), $user->getId());
    }

    /**
     * Log in a user object automatically without pass authentication
     *
     * @param $user
     * @return \Tk\Auth\Result
     */
    public function autoAuthenticate($user)
    {
        $auth = $this->getConfig()->getAuth();
        \App\Listener\MasqueradeHandler::masqueradeClear();
        $authResult = new \Tk\Auth\Result(\Tk\Auth\Result::SUCCESS, $user->getId());
        $auth->clearIdentity()->getStorage()->write($user->getId());
        $this->getConfig()->setUser($user);
        return $authResult;
    }

    /**
     * Return the Uri to redirect to on successful LTI login
     *
     * @param \App\Db\User $user
     * @param \App\Db\Subject $subject
     * @return \Tk\Uri
     * @throws \Exception
     */
    public function getLtiHome($user, $subject)
    {
        return $user->getHomeUrl();
    }


    /**
     * @return \App\Config
     */
    public function getConfig()
    {
        return \App\Config::getInstance();
    }

}