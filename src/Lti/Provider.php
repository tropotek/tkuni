<?php
namespace Lti;

use IMSGlobal\LTI\ToolProvider;

/**
 * Class Provider
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 *
 * @todo Should we optimize the entire app level LTI objects to be more abstract ?????
 * @todo I have implemented this as a working example primarily
 *
 */
class Provider extends ToolProvider\ToolProvider
{
    const LTI_LAUNCH = 'lti.launch';
    const LTI_COURSE_ID = 'lti.courseId';

    /**
     * @var \App\Db\Institution
     */
    protected $institution = null;

    /**
     * @var \Tk\EventDispatcher\EventDispatcher
     */
    protected $dispatcher = null;

    /**
     * @var \App\Db\Course
     */
    protected static $course = null;

    /**
     * Provider constructor.
     *
     * @param \IMSGlobal\LTI\ToolProvider\DataConnector\DataConnector $dataConnector
     * @param \App\Db\Institution $institution
     * @param \Tk\EventDispatcher\EventDispatcher $dispatcher
     */
    public function __construct(\IMSGlobal\LTI\ToolProvider\DataConnector\DataConnector $dataConnector, $institution = null, $dispatcher = null)
    {
        parent::__construct($dataConnector);
        $this->institution = $institution;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Get the LTI session data array
     *
     * @return array
     */
    public static function getLtiSession()
    {
        return \App\Factory::getSession()->get(self::LTI_LAUNCH);
    }

    /**
     * Get the LTi session course
     *
     * @return \App\Db\Course|\Tk\Db\Map\Model
     */
    public static function getLtiCourse()
    {
        if (!self::$course) {
            $ltiSes = self::getLtiSession();
            self::$course = \App\Db\CourseMap::create()->find($ltiSes[self::LTI_COURSE_ID]);
        }
        return self::$course;
    }

    /**
     * Get the LTi session institution
     *
     * @return \App\Db\Institution|\Tk\Db\Map\Model
     */
    public static function getLtiInstitution()
    {
        return self::getLtiCourse()->getInstitution();
    }


    /**
     * Insert code here to handle incoming connections - use the user,
     * context and resourceLink properties of the class instance
     * to access the current user, context and resource link.
     *
     * The onLaunch method may be used to:
     *
     *  - create the user account if it does not already exist (or update it if it does);
     *  - create any workspace required for the resource link if it does not already exist (or update it if it does);
     *  - establish a new session for the user (or otherwise log the user into the tool provider application);
     *  - keep a record of the return URL for the tool consumer (for example, in a session variable);
     *  - set the URL for the home page of the application so the user may be redirected to it.
     *
     */
    function onLaunch()
    {
        try {
            if (!$this->user->email) {
                throw new \Tk\Exception('User email not found! Cannot log in.');
            }

            // Try to locate an existing user...
            $user = \App\Db\UserMap::create()->findByEmail($this->user->email, $this->institution->id);

            if (!$user) {
                // Create new user
                $role = \App\Auth\Acl::ROLE_STUDENT;
                if ($this->user->isLearner() || $this->user->isStaff()) {
                    $role = \App\Auth\Acl::ROLE_STAFF;
                }

                list($username, $domain) = explode('@', $this->user->email);
                // There is a possibility that the usernames clash so auto create a unique one.
                $un = $username;
                $i = 0;
                $found = null;
                do {
                    $found = \App\Db\UserMap::create()->findByUsername($un, $this->institution->id);
                    if (!$found) {
                        $username = $un;
                    }
                    $un = $username.'_'.$i;
                    $i++;
                } while ($found);

                $user = \App\Factory::createNewUser($this->institution->id, $username, $this->user->email, $role, '', $this->user->fullname);
            }

            if (!$user->active) {
                throw new \Tk\Exception('User has no permission to access this resource. Contact your administrator.');
            }

            // Add user to auth
            $auth = \App\Factory::getAuth();
            $auth->clearIdentity()->getStorage()->write(array('username' => $user->username, 'institutionId' => $user->institutionId));

            // Add user to course if found.
            if (empty($_POST['context_label'])) throw new \Tk\Exception('Course not available, Please contact LMS administrator.');

            $courseCode = preg_replace('/[^a-z0-9_-]/i', '_', $_POST['context_label']);
            $course = \App\Db\CourseMap::create()->findByCode($courseCode, $this->institution->id);
            if (!$course) {
                if (!$this->user->isStaff()) throw new \Tk\Exception('Course not available, Please contact course coordinator.');
                $course = new \App\Db\Course();
                $course->institutionId = $this->institution->id;
                $course->name = $_POST['context_title'];
                $course->code = $courseCode;
                $course->email = empty($_POST['lis_person_contact_email_primary']) ? $_POST['lis_person_contact_email_primary'] : \Tk\Config::getInstance()->get('site.email');
                $course->description = '';
                $course->start = \Tk\Date::create();
                $course->finish = \Tk\Date::create()->add(new \DateInterval('P1Y'));
                $course->active = true;
                $course->save();
            }
            \App\Db\CourseMap::create()->addUser($course->id, $user->id);

            $arr = array_merge($_GET, $_POST);
            $arr[self::LTI_COURSE_ID] = $course->id;
            \Tk\Session::getInstance()->set(self::LTI_LAUNCH, $arr);

            // fire loginSuccess....
            if ($this->dispatcher) {    // This event should redirect the user to their homepage.
                $event = new \Tk\Event\AuthEvent($auth, $_POST);
                $event->set('user', $user);
                $this->dispatcher->dispatch(\Tk\Auth\AuthEvents::LOGIN_SUCCESS, $event);
            }
        } catch (\Exception $e) {
            vd($e->__toString());
            $this->reason = $e->__toString();
            $this->message = $e->getMessage();  // This will be shown in the host app
            $this->ok = false;
            return;
        }


    }

    /**
     * Insert code here to handle incoming content-item requests - use the user and context
     * properties to access the current user and context.
     *
     */
    function onContentItem()
    {
        vd('LTI: onContentItem');
    }

    /**
     * Insert code here to handle incoming registration requests - use the user
     * property of the $tool_provider parameter to access the current user.
     *
     */
    function onRegister()
    {
        vd('LTI: onRegister');
    }

    /**
     * Insert code here to handle errors on incoming connections - do not expect
     * the user, context and resourceLink properties to be populated but check the reason
     * property for the cause of the error.
     * Return TRUE if the error was fully handled by this method.
     *
     * @return null|bool
     */
    function onError()
    {
        vd('LTI: onError');
        /** @var \Psr\Log\LoggerInterface $log */
        $log = \App\Factory::getConfig()->getLog();
        if ($log) {
            $log->error($this->reason . ' ' . $this->message);
        }
        return true;        // Stops redirect back to app, in-case you want to show an error messages locally
    }

}