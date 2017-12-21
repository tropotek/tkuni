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
     * @param $courseId
     * @return null|\Tk\Db\Map\Model|\app\Db\Course
     */
    public function findCourse($courseId)
    {
        return \App\Db\CourseMap::create()->find($courseId);
    }

    /**
     * @param $courseCode
     * @param $institutionId
     * @return null|Db\Course|\Tk\Db\ModelInterface
     */
    public function findCourseByCode($courseCode, $institutionId)
    {
        return \App\Db\CourseMap::create()->findByCode($courseCode, $institutionId);
    }

    /**
     * @param $params
     * @return Db\Course|null
     */
    public function createCourse($params)
    {
        $course = null;
        switch($params['type']) {
            case 'lti':
            case 'ldap':
                $course = new \App\Db\Course();
                \App\Db\CourseMap::create()->mapForm($params, $course);
                $course->save();
                $this->addUserToCourse($course, $params['UserIface']);
        }
        return $course;
    }

    /**
     * @param \App\Db\Course $course
     * @param \app\Db\User $user
     */
    public function addUserToCourse($course, $user)
    {
        \App\Db\CourseMap::create()->addUser($course->getId(), $user->getId());
    }

    /**
     * Log in a user object automatically without pass authentication
     *
     * @param $user
     * @return \Tk\Auth\Result
     */
    public function autoAuthenticate($user)
    {
        $auth = \App\Config::getInstance()->getAuth();
        \App\Listener\MasqueradeHandler::masqueradeClear();
        $authResult = new \Tk\Auth\Result(\Tk\Auth\Result::SUCCESS, $user->id);
        $auth->clearIdentity()->getStorage()->write($user->id);
        \App\Config::getInstance()->setUser($user);
        return $authResult;
    }

    /**
     * Return the Uri to redirect to on successful LTI login
     *
     * @param \App\Db\User $user
     * @param \App\Db\Course $course
     * @return \Tk\Uri
     * @throws \Exception
     */
    public function getLtiHome($user, $course)
    {
        return $user->getHomeUrl();
    }


}