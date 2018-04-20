<?php
namespace App\Controller\User;

use Tk\Request;
use Dom\Template;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Edit extends \Uni\Controller\AdminIface
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
     * @var null|\Tk\Uri
     */
    protected $url = null;


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * setPageHeading
     */
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
     * @param Request $request
     * @throws Form\Exception
     * @throws \ReflectionException
     * @throws \Tk\Db\Exception
     * @throws \Tk\Exception
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->setPageHeading();
        $this->institution = $this->getUser()->getInstitution();

        $this->user = new \App\Db\User();
        $this->user->role = $this->getUser()->role;
        if ($this->user->isClient()) {
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

        if (!$this->url)
            $this->url = \Uni\Uri::createHomeUrl('/userManager.html');

        $this->form = \App\Config::getInstance()->createForm('userEdit');
        $this->form->setRenderer(\App\Config::getInstance()->createFormRenderer($this->form));

        if (!$this->getuser()->isStudent()) {
            $this->form->addField(new Field\Input('name'))->setTabGroup('Details')->setRequired(true);
        } else {
            $this->form->addField(new Field\Html('name'))->setTabGroup('Details')->setRequired(true);
        }
        $this->form->addField(new Field\Input('displayName'))->setTabGroup('Details')->setRequired(true);
        if ($this->getUser()->isAdmin() || $this->getUser()->isClient()) {
            $this->form->addField(new Field\Input('username'))->setTabGroup('Details')->setRequired(true);
            $this->form->addField(new Field\Input('email'))->setTabGroup('Details')->setRequired(true);
        } else {
            $this->form->addField(new Field\Html('username'))->setTabGroup('Details');
            $this->form->addField(new Field\Html('email'))->setTabGroup('Details');
        }
        if ($this->user->hasRole(array(\App\Db\User::ROLE_STAFF, \App\Db\User::ROLE_STUDENT))) {
            $this->form->addField(new Field\Input('uid'))->setLabel('UID')->setTabGroup('Details')->setNotes('The student or staff number assigned by the institution.');
        }
        if ($this->getUser()->isAdmin()) {
            if ($this->getUser()->hasRole(array(\App\Db\User::ROLE_STAFF, \App\Db\User::ROLE_CLIENT))) {
                $list = array('-- Select --' => '', 'Staff' => \App\Db\User::ROLE_STAFF, 'Student' => \App\Db\User::ROLE_STUDENT);
                $this->form->addField(new Field\Select('role', $list))->setNotes('Select the access level for this user')->setRequired(true)->setTabGroup('Details');
            }
        }
        if (!$this->getuser()->isStudent()) {
            $this->form->addField(new Field\Checkbox('active'))->setTabGroup('Details');
        }

        if ($this->getUser()->isAdmin()) {
            $this->form->setAttr('autocomplete', 'off');
            $f = $this->form->addField(new Field\Password('newPassword'))->setAttr('placeholder', 'Click to edit')->
            setAttr('readonly', 'true')->setAttr('onfocus', "this.removeAttribute('readonly');this.removeAttribute('placeholder');")->setTabGroup('Password');
            if (!$this->user->getId())
                $f->setRequired(true);
            $f = $this->form->addField(new Field\Password('confPassword'))->setAttr('placeholder', 'Click to edit')->
            setAttr('readonly', 'true')->setAttr('onfocus', "this.removeAttribute('readonly');this.removeAttribute('placeholder');")->setNotes('Change this users password.')->setTabGroup('Password');
            if (!$this->user->getId())
                $f->setRequired(true);
        }

        //if ($this->user->id && $this->getUser()->isClient() ) {
        if ($this->user->id && ($this->getUser()->isStaff() || $this->getUser()->isClient()) ) {
            $list = \Tk\Form\Field\Option\ArrayObjectIterator::create(\App\Db\SubjectMap::create()->findActive($this->institution->id));
            $this->form->addField(new Field\Select('selSubject[]', $list))->setLabel('Subject Selection')->setNotes('This list only shows active and enrolled subjects. Use the enrollment form in the edit subject page if your subject is not visible.')->
                setTabGroup('Subjects')->addCss('tk-dualSelect')->setAttr('data-title', 'Subjects');
            $arr = \App\Db\SubjectMap::create()->findByUserId($this->user->id)->toArray('id');
            $this->form->setFieldValue('selSubject', $arr);
        }

        $this->form->addField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->form->addField(new Event\Submit('save', array($this, 'doSubmit')));

        $this->form->addField(new Event\Link('cancel', $this->url));
        
        $this->form->load(\App\Db\UserMap::create()->unmapForm($this->user));
        
        $this->form->execute();
        
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
            $this->user->password = \App\Config::getInstance()->hashPassword($this->form->getFieldValue('newPassword'), $this->user);
        }

        // Add user to institution
        if ($this->institution) {
            $this->user->institutionId = $this->institution->id;

            // TODO: Add the ability to assign a staff member to subjects.
            $selected = $form->getFieldValue('selSubject');
            if ($this->user->id && is_array($selected)) {
                $list = \App\Db\SubjectMap::create()->findActive($this->institution->id);
                /** @var \App\Db\Subject $subject */
                foreach ($list as $subject) {
                    if (in_array($subject->id, $selected)) {
                        \App\Db\SubjectMap::create()->addUser($subject->id, $this->user->id);
                    } else {
                        \App\Db\SubjectMap::create()->removeUser($subject->id, $this->user->id);
                    }
                }
            }
        }
        $this->user->save();

        \Tk\Alert::addSuccess('User record saved!');
        if ($form->getTriggeredEvent()->getName() == 'update') {
            $this->url->redirect();
        }
        \Tk\Uri::create()->set('userId', $this->user->id)->redirect();
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        // Render the form
        $template->insertTemplate('form', $this->form->getRenderer()->show());
        
        if ($this->user->id) {
            $template->insertText('username', $this->user->name . ' - [UID ' . $this->user->id . ']');
            $template->setChoice('update');
        } else {
            $template->insertText('username', 'Create User');
            $template->setChoice('new');
        }

        if (\App\Listener\MasqueradeHandler::canMasqueradeAs($this->getUser(), $this->user)) {
            $this->getActionPanel()->addButton(\Tk\Ui\Button::create('Masquerade',
                \Uni\Uri::create()->reset()->set(\App\Listener\MasqueradeHandler::MSQ, $this->user->hash), 'fa fa-user-secret'))->addCss('tk-masquerade');
        }
        return $template;
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
    <div class="panel-heading"><i class="fa fa-user fa-fw"></i> <span var="username"></span></div>
    <div class="panel-body">
      <div var="form"></div>
    </div>
  </div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}