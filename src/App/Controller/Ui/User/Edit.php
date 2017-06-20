<?php
namespace App\Controller\Ui\User;

use Tk\Request;
use Dom\Template;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;
use App\Controller\Iface;

/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Edit extends Iface
{

    /**
     * @var Form
     */
    protected $form = null;

    /**
     * @var \App\Db\User
     */
    private $user = null;

    /**
     * @var \App\Db\Institution
     */
    private $institution = null;


    /**
     *
     */
    public function __construct()
    {
        parent::__construct('User Edit');
    }


    public function setPageHeading()
    {
        switch($this->getUser()->role) {
            case \App\Db\User::ROLE_ADMIN:
                $this->setPageTitle('Administration User Edit');
                break;
            case \App\Db\User::ROLE_CLIENT:
                $this->setPageTitle('Staff/Student Edit');
                break;
            case \App\Db\User::ROLE_STAFF:
                $this->setPageTitle('Staff/Student Edit');
                break;
        }
    }

    /**
     *
     * @param Request $request
     * @return \App\Page\Iface|Template|string
     * @throws \Tk\Exception
     */
    public function doDefault(Request $request)
    {
        $this->setPageHeading();
        $this->institution = $this->getUser()->getInstitution();

        $this->user = new \App\Db\User();
        $this->user->role = $this->getUser()->role;
        if ($this->user->hasRole(\App\Db\User::ROLE_CLIENT)) {
            $this->user->role = \App\Db\User::ROLE_STAFF;
        }

        if ($request->has('userId')) {
            $this->user = \App\Db\UserMap::create()->find($request->get('userId'));
            if (!$this->user) {
                throw new \Tk\Exception('Invalid user account.');
            }
            if ($this->institution && $this->institution->id != $this->user->getInstitution()->id) {
                throw new \Tk\Exception('Invalid user account.');
            }
        }

        $this->form = new Form('formEdit');

        $this->form->addField(new Field\Input('name'))->setRequired(true)->setTabGroup('Details');
        $this->form->addField(new Field\Input('username'))->setRequired(true)->setTabGroup('Details');
        $this->form->addField(new Field\Input('email'))->setRequired(true)->setTabGroup('Details');
        if ($this->user->hasRole(array(\App\Db\User::ROLE_STAFF, \App\Db\User::ROLE_STUDENT))) {
            $this->form->addField(new Field\Input('uid'))->setLabel('UID')->setTabGroup('Details')->setNotes('The student or staff number assigned by the institution.');
        }
        if ($this->getUser()->hasRole(array(\App\Db\User::ROLE_STAFF, \App\Db\User::ROLE_CLIENT))) {
            $list = array('-- Select --' => '', 'Staff' => \App\Db\User::ROLE_STAFF, 'Student' => \App\Db\User::ROLE_STUDENT);
            $this->form->addField(new Field\Select('role', $list))->setNotes('Select the access level for this user')->setTabGroup('Details')->setRequired(true);
        }
        $this->form->addField(new Field\Checkbox('active'))->setTabGroup('Details');

        $this->form->setAttr('autocomplete', 'off');
        $f = $this->form->addField(new Field\Password('newPassword'))->setAttr('placeholder', 'Click to edit')->
            setAttr('readonly', 'true')->setAttr('onfocus', "this.removeAttribute('readonly');this.removeAttribute('placeholder');")->setTabGroup('Password');
        if (!$this->user->getId())
            $f->setRequired(true);
        $f = $this->form->addField(new Field\Password('confPassword'))->setAttr('placeholder', 'Click to edit')->
            setAttr('readonly', 'true')->setAttr('onfocus', "this.removeAttribute('readonly');this.removeAttribute('placeholder');")->setNotes('Change this users password.')->setTabGroup('Password');
        if (!$this->user->getId())
            $f->setRequired(true);

        if ($this->user->id && ($this->getUser()->hasRole(\App\Db\User::ROLE_STAFF) || $this->getUser()->hasRole(\App\Db\User::ROLE_CLIENT)) ) {
            $list = \Tk\Form\Field\Option\ArrayObjectIterator::create(\App\Db\CourseMap::create()->findActive($this->institution->id));
            $this->form->addField(new Field\Select('selCourse[]', $list))->setLabel('Course Selection')->setNotes('This list only shows active and enrolled courses. Use the enrollment form in the edit course page if your course is not visible.')->
                setTabGroup('Courses')->addCss('tk-dualSelect')->setAttr('data-title', 'Courses');
            $arr = \App\Db\CourseMap::create()->findByUserId($this->user->id)->toArray('id');
            $this->form->setFieldValue('selCourse', $arr);
        }

        $this->form->addField(new Event\Button('update', array($this, 'doSubmit')));
        $this->form->addField(new Event\Button('save', array($this, 'doSubmit')));

        $url = \App\Uri::createHomeUrl('/userManager.html');
        $this->form->addField(new Event\Link('cancel', $url));
        
        $this->form->load(\App\Db\UserMap::create()->unmapForm($this->user));
        
        $this->form->execute();
        
        return $this->show();
    }

    /**
     * @param \Tk\Form $form
     */
    public function doSubmit($form)
    {
        // Load the object with data from the form using a helper object
        \App\Db\UserMap::create()->mapForm($form->getValues(), $this->user);

        // TODO: We have a unique issue here where if a user is to be created
        // TODO:  and the record has been marked deleted, then it will throw an error
        // TODO:  that the email/username, already exists. Should we locate that record
        // TODO:  and update/undelete it?

        // Password validation needs to be here
        if ($this->form->getFieldValue('newPassword')) {
            if ($this->form->getFieldValue('newPassword') != $this->form->getFieldValue('confPassword')) {
                $form->addFieldError('newPassword', 'Passwords do not match.');
                $form->addFieldError('confPassword');
            }
        }
        if (!$this->user->id && !$this->form->getFieldValue('newPassword')) {
            $form->addFieldError('newPassword', 'Please enter a new password.');
        }

        $form->addFieldErrors($this->user->validate());


        if ($form->hasErrors()) {
            return;
        }
        // Hash the password correctly
        if ($this->form->getFieldValue('newPassword')) {
            $this->user->password = \App\Factory::hashPassword($this->form->getFieldValue('newPassword'), $this->user);
        }

        // Add user to institution
        if ($this->institution) {
            $this->user->institutionId = $this->institution->id;

            // TODO: Add the ability to assign a staff member to courses.
            if ($this->user->id) {
                $list = \App\Db\CourseMap::create()->findActive($this->institution->id);
                $selected = $form->getFieldValue('selCourse');
                /** @var \App\Db\Course $course */
                foreach ($list as $course) {
                    if (in_array($course->id, $selected)) {
                        \App\Db\CourseMap::create()->addUser($course->id, $this->user->id);
                    } else {
                        \App\Db\CourseMap::create()->deleteUser($course->id, $this->user->id);
                    }
                }
            }
        }
        $this->user->save();

        \Tk\Alert::addSuccess('User record saved!');
        if ($form->getTriggeredEvent()->getName() == 'update') {
            \App\Uri::createHomeUrl('/userManager.html')->redirect();
        }
        \Tk\Uri::create()->set('userId', $this->user->id)->redirect();
    }

    /**
     * @return \App\Page\Iface
     */
    public function show()
    {
        $template = $this->getTemplate();
        
        if ($this->user->id) {
            $template->insertText('username', $this->user->name . ' - [UID ' . $this->user->id . ']');
            $template->setChoice('update');
        } else {
            $template->insertText('username', 'Create User');
            $template->setChoice('new');
        }

        // Render the form
        $fren = new \Tk\Form\Renderer\Dom($this->form);
        $template->appendTemplate($this->form->getId(), $fren->show()->getTemplate());

        //if ($this->user->id && $this->user->id != $this->getUser()->id) {
        if ($this->user->id) {
            $template->setAttr('msq', 'href', \App\Uri::create()->reset()->set(\App\Listener\MasqueradeHandler::MSQ, $this->user->hash));
            $template->setChoice('msq');
        }

        return $this->getPage()->setPageContent($this->getTemplate());
    }


    /**
     * DomTemplate magic method
     *
     * @return Template
     */
    public function __makeTemplate()
    {

        $xhtml = <<<HTML
<div class="">

  <div class="panel panel-default">
    <div class="panel-heading">
      <i class="fa fa-cogs fa-fw"></i> Actions
    </div>
    <div class="panel-body">
      <a href="javascript: window.history.back();" class="btn btn-default"><i class="fa fa-arrow-left"></i>
        <span>Back</span></a>
      <a href="javascript:;" class="btn btn-default" choice="msq" var="msq"><i class="fa fa-user-secret"></i> <span>Masquerade</span></a>
    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading">
      <i class="fa fa-user fa-fw"></i> <span var="username"></span>
    </div>
    <div class="panel-body">
      <div var="formEdit"></div>
    </div>
  </div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}