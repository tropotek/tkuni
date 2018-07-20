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
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setPageTitle('Settings');
        $this->getCrumbs()->reset();
    }

    /**
     * doDefault
     *
     * @param Request $request
     * @return void
     * @throws Form\Exception
     * @throws \Tk\Db\Exception
     * @throws \Tk\Exception
     */
    public function doDefault(Request $request)
    {

        $this->data = \Tk\Db\Data::create();

        $this->getActionPanel()->add(\Tk\Ui\Button::create('Plugins', \Tk\Uri::create('/admin/plugins.html'), 'fa fa-plug'));

        $this->form = Form::create('formEdit');
        $this->form->setRenderer(new \Tk\Form\Renderer\Dom($this->form));

        $this->form->addField(new Field\Input('site.title'))->setLabel('Site Title')->setRequired(true);
        $this->form->addField(new Field\Input('site.email'))->setLabel('Site Email')->setRequired(true);
        $this->form->addField(new Field\Input('site.meta.keywords'))->setLabel('SEO Keywords')->setRequired(true);
        $this->form->addField(new Field\Input('site.meta.description'))->setLabel('SEO Description')->setRequired(true);
        $this->form->addField(new Field\Input('site.google.map.key'))->setLabel('Google API Key')
            ->setNotes('<a href="https://cloud.google.com/maps-platform/" target="_blank">Get Google Maps Api Key</a> And be sure to enable `Maps Javascript API`, `Maps Embed API` and `Places API for Web` for this site.');

//        $this->form->addField(new Field\Checkbox('site.client.registration'))->setLabel('Client Registration')
//            ->setNotes('Enable Client registrations to be submitted');

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
     * @param \Tk\Form\Event\Iface $event
     * @throws \Tk\Db\Exception
     */
    public function doSubmit($form, $event)
    {
        $values = $form->getValues();
        $this->data->replace($values);

        if (empty($values['site.title']) || strlen($values['site.title']) < 3) {
            $form->addFieldError('site.title', 'Please enter your name');
        }
        if (empty($values['site.email']) || !filter_var($values['site.email'], \FILTER_VALIDATE_EMAIL)) {
            $form->addFieldError('site.email', 'Please enter a valid email address');
        }

        if ($this->form->hasErrors()) {
            return;
        }

        $this->data->save();

        \Tk\Alert::addSuccess('Site settings saved.');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create());
        }
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        //$this->getActionPanel()->add(\Tk\Ui\Button::create('Users', \Tk\Uri::create('/admin/userManager.html'), 'fa fa-users'));

        // Render the form
        $template->insertTemplate('form', $this->form->getRenderer()->show());

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
      <i class="fa fa-cog"></i> Site Settings
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