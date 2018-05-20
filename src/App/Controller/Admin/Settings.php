<?php
namespace App\Controller\Admin;

use Tk\Request;
use Tk\Form;
use Tk\Form\Event;
use Tk\Form\Field;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Settings extends \Uni\Controller\AdminIface
{

    /**
     * @var Form
     */
    protected $form = null;

    /**
     * @var \Tk\Db\Data
     */
    protected $data = null;


    /**
     * doDefault
     *
     * @param Request $request
     * @throws \Tk\Exception
     * @throws \Exception
     * @throws Form\Exception
     * @throws Form\Exception
     * @throws Form\Exception
     * @throws Form\Exception
     * @throws Form\Exception
     * @throws Form\Exception
     * @throws Form\Exception
     */
    public function doDefault(Request $request)
    {
        $this->setPageTitle('Site Settings');
        $this->data = \Tk\Db\Data::create();
        
        $this->form = \App\Config::getInstance()->createForm('settingsEdit');
        $this->form->setRenderer(\App\Config::getInstance()->createFormRenderer($this->form));

        $this->form->addField(new Field\Input('site.title'))->setLabel('Site Title')->setRequired(true);
        $this->form->addField(new Field\Input('site.email'))->setLabel('Site Email')->setRequired(true);
        $this->form->addField(new Field\Checkbox('site.client.registration'))->setLabel('Client Registration')->setNotes('Allow users to create new accounts');
        $this->form->addField(new Field\Checkbox('site.client.activation'))->setLabel('Client Activation')->setNotes('Allow users to activate their own accounts');

        $this->form->addField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->form->addField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->form->addField(new Event\LinkButton('cancel', \Tk\Uri::create('/admin/index.html')));

        $this->form->load($this->data->toArray());
        $this->form->execute();

    }

    /**
     * doSubmit()
     *
     * @param Form $form
     * @throws \Tk\Db\Exception
     */
    public function doSubmit($form)
    {
        $values = $form->getValues();
        $this->data->replace($values);
        
        if (!$form->getFieldValue('site.title')) {
            $form->addFieldError('site.title', 'Please enter the sites title');
        }
        if ($form->getFieldValue('site.email') && !filter_var($form->getFieldValue('site.email'), \FILTER_VALIDATE_EMAIL)) {
            $form->addFieldError('site.email', 'Please enter a valid email address');
        }
        
        if ($this->form->hasErrors()) {
            return;
        }
        
        $this->data->save();
        
        \Tk\Alert::addSuccess('Site settings saved.');
        if ($form->getTriggeredEvent()->getName() == 'update') {
            \Tk\Uri::create('/admin/index.html')->redirect();
        }
        \Tk\Uri::create()->redirect();
    }

    /**
     * show()
     *
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        // Render the form
        $template->insertTemplate('form', $this->form->getRenderer()->show());

        $this->getActionPanel()->addButton(\Tk\Ui\Button::create('Site Plugins', \Uni\Uri::createHomeUrl('/plugins.html'), 'fa fa-plug'));

        return $template;
    }

    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div>

  <div class="panel panel-default">
    <div class="panel-heading">
      <i class="glyphicon glyphicon-cog"></i> Site Settings
    </div>
    <div class="panel-body">
      <div var="form"></div>
    </div>
  </div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }
}