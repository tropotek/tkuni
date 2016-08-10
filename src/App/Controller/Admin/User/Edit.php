<?php
namespace App\Controller\Admin\User;

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
     *
     */
    public function __construct()
    {
        parent::__construct('User Edit');
    }

    /**
     *
     * @param Request $request
     * @return \App\Page\Iface|Template|string
     * @throws \Tk\Exception
     */
    public function doDefault(Request $request)
    {
        $this->user = new \App\Db\User();
        $this->user->role = \App\Auth\Acl::ROLE_ADMIN;
        if ($request->get('userId')) {
            $this->user = \App\Db\User::getMapper()->find($request->get('userId'));
            if ($this->user->role != \App\Auth\Acl::ROLE_ADMIN) {
                throw new \Tk\Exception('Invalid user account.');
            }
        }

        $this->form = new Form('formEdit');

        $this->form->addField(new Field\Input('username'))->setRequired(true)->setTabGroup('Details');
        $this->form->addField(new Field\Input('name'))->setRequired(true)->setTabGroup('Details');
        $this->form->addField(new Field\Input('email'))->setRequired(true)->setTabGroup('Details');

        //$list = array('-- Select --' => '', 'Admin' => \App\Auth\Access::ROLE_ADMIN, 'Client' => \App\Auth\Access::ROLE_CLIENT, 'Staff' => \App\Auth\Access::ROLE_STAFF, 'Student' => \App\Auth\Access::ROLE_STUDENT);
//        $list = array('-- Select --' => '', 'Admin' => \App\Auth\Acl::ROLE_ADMIN, 'Client' => \App\Auth\Acl::ROLE_CLIENT);
//        if (!in_array($this->user->role, $list)) {
//            $list = array('-- Select --' => '', 'Staff' => \App\Auth\Acl::ROLE_STAFF, 'Student' => \App\Auth\Acl::ROLE_STUDENT);
//        }
        //$this->form->addField(new Field\Select('role', $list))->setNotes('Select the access level for this user')->setRequired(true)->setTabGroup('Details')->setRequired(true);
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
        $this->form->addField(new Event\Link('cancel', \Tk\Uri::create('/admin/userManager.html')));
        
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

        \App\Alert::addSuccess('User record saved!');
        if ($form->getTriggeredEvent()->getName() == 'update') {
            \Tk\Uri::create('/admin/userManager.html')->redirect();
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