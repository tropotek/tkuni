<?php
namespace App\Controller\Ui\User;

use Tk\Request;
use Dom\Template;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;
use \App\Controller\Iface;

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
            case \App\Auth\Acl::ROLE_ADMIN:
                $this->setPageTitle('Administration User Edit');
                break;
            case \App\Auth\Acl::ROLE_CLIENT:
                $this->setPageTitle('Staff/Student Edit');
                break;
            case \App\Auth\Acl::ROLE_STAFF:
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
        if ($this->user->hasRole(array(\App\Auth\Acl::ROLE_STAFF, \App\Auth\Acl::ROLE_STUDENT))) {
            $this->form->addField(new Field\Input('uid'))->setLabel('UID')->setTabGroup('Details')->setNotes('The student or staff number assigned by the institution.');
        }
        if ($this->getUser()->hasRole(\App\Auth\Acl::ROLE_STAFF)) {
            $list = array('-- Select --' => '', 'Staff' => \App\Auth\Acl::ROLE_STAFF, 'Student' => \App\Auth\Acl::ROLE_STUDENT);
            $this->form->addField(new Field\Select('role', $list))->setNotes('Select the access level for this user')->setRequired(true)->setTabGroup('Details')->setRequired(true);
        }
        $this->form->addField(new Field\Checkbox('active'))->setTabGroup('Details');

        $this->form->setAttr('autocomplete', 'off');
        $f = $this->form->addField(new Field\Password('newPassword'))->setAttr('placeholder', 'Click to edit')->setAttr('readonly', 'true')->setAttr('onfocus', "this.removeAttribute('readonly');this.removeAttribute('placeholder');")->setTabGroup('Password');
        if (!$this->user->getId())
            $f->setRequired(true);
        $f = $this->form->addField(new Field\Password('confPassword'))->setAttr('placeholder', 'Click to edit')->setAttr('readonly', 'true')->setAttr('onfocus', "this.removeAttribute('readonly');this.removeAttribute('placeholder');")->setNotes('Change this users password.')->setTabGroup('Password');
        if (!$this->user->getId())
            $f->setRequired(true);



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

        $form->addFieldErrors(\App\Db\UserValidator::create($this->user)->getErrors());

        if ($form->hasErrors()) {
            return;
        }
        // Hash the password correctly
        if ($this->form->getFieldValue('newPassword')) {
            $this->user->password = \App\Factory::hashPassword($this->form->getFieldValue('newPassword'), $this->user);
        }

        $this->user->save();

        // Add user to institution
        if ($this->institution) {
            \App\Db\InstitutionMap::create()->addUser($this->institution->id, $this->user->id);

            // TODO: Add the ability to assign a staff member to courses.
        }


        \App\Alert::addSuccess('User record saved!');
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
        
        if ($this->user->id)
            $template->insertText('username', $this->user->name . ' - [UID ' . $this->user->id . ']');
        else
            $template->insertText('username', 'Create User');
        
        // Render the form
        $fren = new \Tk\Form\Renderer\Dom($this->form);
        $template->appendTemplate($this->form->getId(), $fren->show()->getTemplate());

        return $this->getPage()->setPageContent($this->getTemplate());
    }


    /**
     * DomTemplate magic method
     *
     * @return Template
     */
    public function __makeTemplate()
    {

        $xhtml = <<<XHTML
<div class="row">

  <div class="col-lg-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="fa fa-cogs fa-fw"></i> Actions
      </div>
      <div class="panel-body ">
        <div class="row">
          <div class="col-lg-12">
            <a href="javascript: window.history.back();" class="btn btn-default"><i class="fa fa-arrow-left"></i> <span>Back</span></a>
            <a href="javascript:;" class="btn btn-default"><i class="fa fa-user-secret"></i> <span>Masquerade</span></a>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="fa fa-user fa-fw"></i>
        <span var="username"></span>
      </div>
      
      <div class="panel-body ">
        <div class="row">
          <div class="col-lg-12" var="formEdit">
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
XHTML;

        return \Dom\Loader::load($xhtml);
    }

}