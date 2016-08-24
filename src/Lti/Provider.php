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

    /**
     * @var \App\Db\Institution
     */
    protected $institution = null;

    /**
     * @var \Tk\EventDispatcher\EventDispatcher
     */
    protected $dispatcher = null;

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
                // TODO: Should new users should be prompted to create a new password when they enter their Dashboard through LTI....?????
                // Maybe not they should be instructed to change their password via access through the LTI.......
            }

            if (!$user->active) {
                throw new \Tk\Exception('User has no permission to access this resource. Contact your administrator.');
            }

            // Add user to auth
            $auth = \App\Factory::getAuth();
            $auth->clearIdentity()->getStorage()->write(array('username' => $user->username, 'institutionId' => $user->institutionId));

            // Add user to course if found.
            $course = \App\Db\CourseMap::create()->findByCode($_POST['context_label'], $this->institution->id);
            if ($course) {
                \App\Db\CourseMap::create()->addUser($course->id, $user->id);
            }
            \Tk\Session::getInstance()->set('lti.launch', array_merge($_GET, $_POST));

            // fire loginSuccess....
            if ($this->dispatcher) {    // This event should redirect the user to their homepage.
                $event = new \App\Event\AuthEvent($auth, $_POST);
                $event->set('user', $user);
                $this->dispatcher->dispatch('auth.onLogin.success', $event);
            }
        } catch (\Exception $e) {
            $this->reason = $e->getMessage();
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
        vd('LTI: onError', $this->reason, $this->message);
        //return true;        // Stops redirect back to app, incase you want to show an error messages locally
    }

}