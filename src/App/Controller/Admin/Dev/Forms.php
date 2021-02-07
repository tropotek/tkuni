<?php
namespace App\Controller\Admin\Dev;

use Tk\Form\Event;
use Tk\Form\Field;

/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Forms extends \Uni\Controller\AdminIface
{

    /**
     * @var \Tk\Form
     */
    protected $form1 = null;

    /**
     * @var \Tk\Form
     */
    protected $form2 = null;

    /**
     * @var \Tk\Form
     */
    protected $form3 = null;


    /**
     */
    public function __construct()
    {
        $this->setPageTitle('Forms');
        //$this->getActionPanel()->setEnabled(false);
    }

    /**
     *
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function doDefault(\Tk\Request $request)
    {

        $this->form1 = $this->getConfig()->createForm('form1');
        $this->form1->setRenderer($this->getConfig()->createFormRenderer($this->form1));

        $tab = null;
        $fieldset = 'Field-1';
        $this->form1->appendField(new Field\Input('field1'))->setTabGroup($tab)->setFieldset($fieldset);
        $this->form1->appendField(new Field\Input('field2'))->setTabGroup($tab)->setFieldset($fieldset);
        $this->form1->appendField(new Field\Input('field3'))->setTabGroup($tab)->setFieldset($fieldset);
        $fieldset = 'Field-2';
        $this->form1->appendField(new Field\Input('field4'))->setTabGroup($tab)->setFieldset($fieldset);
        $this->form1->appendField(new Field\Input('field5'))->setTabGroup($tab)->setFieldset($fieldset);
        $this->form1->appendField(new Field\Input('field6'))->setTabGroup($tab)->setFieldset($fieldset);
        $fieldset = 'Field-3';
        $this->form1->appendField(new Field\Input('field7'))->setTabGroup($tab)->setFieldset($fieldset);
        $this->form1->appendField(new Field\Input('field8'))->setTabGroup($tab)->setFieldset($fieldset);
        $this->form1->appendField(new Field\Input('field9'))->setTabGroup($tab)->setFieldset($fieldset);

        $this->form1->appendField(new Field\Input('field10'))->setTabGroup($tab);
        $this->form1->appendField(new Field\DateRange('field11'))->setTabGroup($tab);
        $this->form1->appendField(new Field\Textarea('field12'))->setTabGroup($tab);

        $this->form1->appendField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->form1->appendField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->form1->appendField(new Event\Link('cancel', $this->getBackUrl()));
        $this->form1->execute();

        ///
        $this->form2 = $this->getConfig()->createForm('form2');
        $this->form2->setRenderer($this->getConfig()->createFormRenderer($this->form2));
        $layout = $this->form2->getRenderer()->getLayout();

        $tab = 'Tab1';
        $fieldset = null;
        $this->form2->appendField(new Field\Input('field1'))->setTabGroup($tab)->setFieldset($fieldset);
        $this->form2->appendField(new Field\Input('field2'))->setTabGroup($tab)->setFieldset($fieldset);
        $this->form2->appendField(new Field\Input('field3'))->setTabGroup($tab)->setFieldset($fieldset);

        $tab = 'Tab2';
        $fieldset = null;
        $this->form2->appendField(new Field\Input('field4'))->setTabGroup($tab)->setFieldset($fieldset);
        $this->form2->appendField(new Field\Input('field5'))->setTabGroup($tab)->setFieldset($fieldset);
        $this->form2->appendField(new Field\Input('field6'))->setTabGroup($tab)->setFieldset($fieldset);

        $tab = 'Tab3';
        $fieldset = null;
        $this->form2->appendField(new Field\Input('field7'))->setTabGroup($tab)->setFieldset($fieldset);
        $this->form2->appendField(new Field\Input('field8'))->setTabGroup($tab)->setFieldset($fieldset);
        $this->form2->appendField(new Field\Input('field9'))->setTabGroup($tab)->setFieldset($fieldset);

        $tab = 'Tab4';
        $this->form2->appendField(new Field\Input('field10'))->setTabGroup($tab);
        $this->form2->appendField(new Field\DateRange('field11'))->setTabGroup($tab);
        $this->form2->appendField(new Field\Textarea('field12'))->setTabGroup($tab);

        $this->form2->appendField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->form2->appendField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->form2->appendField(new Event\Link('cancel', $this->getBackUrl()));
        $this->form2->execute();

        ///
        $this->form3 = $this->getConfig()->createForm('form3');
        $this->form3->setRenderer($this->getConfig()->createFormRenderer($this->form3));
        $layout = $this->form3->getRenderer()->getLayout();

        $layout->addRow('field1', 'col');
        $layout->removeRow('field2', 'col');
        $layout->removeRow('field3', 'col');

        $layout->addRow('field4', 'col');
        $layout->removeRow('field5', 'col');
        $layout->removeRow('field6', 'col');

        $layout->addRow('field7', 'col');
        $layout->removeRow('field8', 'col');
        $layout->removeRow('field9', 'col');

        $layout->removeRow('field11', 'col');



        $tab = 'Tab1';
        $fieldset = 'Field-1';
        $this->form3->appendField(new Field\Input('field1'))->setTabGroup($tab)->setFieldset($fieldset);
        $this->form3->appendField(new Field\Input('field2'))->setTabGroup($tab)->setFieldset($fieldset);
        $this->form3->appendField(new Field\Input('field3'))->setTabGroup($tab)->setFieldset($fieldset);

        $fieldset = 'Field-2';
        $this->form3->appendField(new Field\Input('field4'))->setTabGroup($tab)->setFieldset($fieldset);
        $this->form3->appendField(new Field\Input('field5'))->setTabGroup($tab)->setFieldset($fieldset);
        $this->form3->appendField(new Field\Input('field6'))->setTabGroup($tab)->setFieldset($fieldset);

        $tab = 'Tab2';
        $fieldset = 'Field-3';
        $this->form3->appendField(new Field\Input('field7'))->setTabGroup($tab)->setFieldset($fieldset);
        $this->form3->appendField(new Field\Input('field8'))->setTabGroup($tab)->setFieldset($fieldset);
        $this->form3->appendField(new Field\Input('field9'))->setTabGroup($tab)->setFieldset($fieldset);

        $tab = 'Tab3';
        $this->form3->appendField(new Field\Input('field10'))->setTabGroup($tab);
        $this->form3->appendField(new Field\DateRange('field11'))->setTabGroup($tab);
        $this->form3->appendField(new Field\Textarea('field12'))->setTabGroup($tab);

        $this->form3->appendField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->form3->appendField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->form3->appendField(new Event\Link('cancel', $this->getBackUrl()));
        $this->form3->execute();




    }
    /**
     * @param \Tk\Form $form
     * @param \Tk\Form\Event\Iface $event
     * @throws \Exception
     */
    public function doSubmit($form, $event)
    {
        // Load the object with data from the form using a helper object
        //$this->getConfig()->getSubjectMapper()->mapForm($form->getValues(), $this->subject);
        //$form->addFieldErrors($this->subject->validate());

        if ($form->hasErrors()) {
            return;
        }

        //$this->subject->save();




        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getConfig()->getBackUrl());
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

        $template->appendTemplate('form1', $this->form1->getRenderer()->show());
        $template->appendTemplate('form2', $this->form2->getRenderer()->show());
        $template->appendTemplate('form3', $this->form3->getRenderer()->show());

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
<div class="">

  <div class="tk-panel" data-panel-title="Form Test #1" data-panel-icon="fa fa-form" var="form1"></div>
  
  <div class="tk-panel" data-panel-title="Form Test #2" data-panel-icon="fa fa-form" var="form2"></div>
  
  <div class="tk-panel" data-panel-title="Form Test #3" data-panel-icon="fa fa-form" var="form3"></div>
  
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}