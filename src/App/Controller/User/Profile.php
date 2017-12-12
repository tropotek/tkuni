<?php
namespace App\Controller\User;

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
     * @param Request $request
     */
    public function doDefault(Request $request)
    {
        $this->setPageTitle('My Profile');
        
        $this->user = $this->getUser();

        $this->form = \App\Factory::createForm('userEdit');
        $this->form->setRenderer(\App\Factory::createFormRenderer($this->form));
        $this->form->setAttr('autocomplete', 'off');

        $this->form->addField(new Field\Input('displayName'))->setTabGroup('Details');
        //$this->form->addField(new Field\Input('phone'))->setTabGroup('Details');
        $this->form->addField(new Field\Input('username'))->setReadonly(true)->setTabGroup('Details');
        $this->form->addField(new Field\Input('email'))->setReadonly(true)->setTabGroup('Details');

        $this->form->addField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->form->addField(new Event\Submit('save', array($this, 'doSubmit')));
        $url = \Tk\Uri::create($this->getUser()->getHomeUrl());
        $this->form->addField(new Event\Link('cancel', $url));

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

        $form->addFieldErrors($this->user->validate());

        if ($form->hasErrors()) {
            return;
        }

        $this->user->save();

        \Tk\Alert::addSuccess('User record saved!');
        if ($form->getTriggeredEvent()->getName() == 'update') {
            \App\Uri::createHomeUrl('/index.html')->redirect();
        }
        \Tk\Uri::create()->redirect();
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        $template->insertText('username', $this->user->name . ' - [UID ' . $this->user->id . ']');

        // Render the form
        $template->insertTemplate('form', $this->form->getRenderer()->show());

        return $template;
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
    <div class="panel-heading"><i class="fa fa-user fa-fw"></i> <span var="username"></span></div>
    <div class="panel-body">
      <div var="form"></div>
    </div>
  </div>

</div>
HTML;

        return \Dom\Loader::load($html);
    }

}