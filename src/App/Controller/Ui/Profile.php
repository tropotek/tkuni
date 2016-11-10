<?php
namespace App\Controller\Ui;

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
class Profile extends Iface
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
        $title = 'My Profile';
        parent::__construct($title);
    }

    /**
     *
     * @param Request $request
     * @return \App\Page\Iface|Template|string
     */
    public function doDefault(Request $request)
    {
        $this->user = $this->getUser();


        $this->form = new Form('formEdit');
        $this->form->setAttr('autocomplete', 'off');

        $this->form->addField(new Field\Input('username'))->setRequired(true)->setTabGroup('Details');
        $this->form->addField(new Field\Input('displayName'))->setRequired(true)->setTabGroup('Details');
        $emailF = $this->form->addField(new Field\Input('email'))->setRequired(true)->setTabGroup('Details');
        //$emailF->setAttr('readonly', 'readonly');

        $this->form->addField(new Field\Password('newPassword'))->setAttr('placeholder', 'Click to edit')->setAttr('readonly', 'true')->setAttr('onfocus', "this.removeAttribute('readonly');this.removeAttribute('placeholder');")->setTabGroup('Password');
        $this->form->addField(new Field\Password('confPassword'))->setAttr('placeholder', 'Click to edit')->setAttr('readonly', 'true')->setAttr('onfocus', "this.removeAttribute('readonly');this.removeAttribute('placeholder');")->setNotes('Change this users password.')->setTabGroup('Password');

        $this->form->addField(new Event\Button('update', array($this, 'doSubmit')));
        $this->form->addField(new Event\Button('save', array($this, 'doSubmit')));
        $url = \Tk\Uri::create($this->getUser()->getHomeUrl());
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
            $this->user->setPassword($this->form->getFieldValue('newPassword'));
        }

        $this->user->save();

        \Ts\Alert::addSuccess('User record saved!');
        if ($form->getTriggeredEvent()->getName() == 'update') {
            \App\Uri::createHomeUrl('/index.html')->redirect();
        }
        \Tk\Uri::create()->redirect();
    }

    /**
     * @return \App\Page\Iface
     */
    public function show()
    {
        $template = $this->getTemplate();

        $template->insertText('username', $this->user->name . ' - [UID ' . $this->user->id . ']');

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

        $html = <<<HTML
<div class="">

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

        return \Dom\Loader::load($html);
    }

}