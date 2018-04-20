<?php
namespace App\Controller\Subject;

use Dom\Template;
use Tk\Form;
use Tk\Form\Event;
use Tk\Form\Field;
use Tk\Request;

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
     * @var \App\Db\Subject
     */
    private $subject = null;

    /**
     * @var \App\Db\Institution
     */
    private $institution = null;


    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     * @param Request $request
     * @throws \Tk\Exception
     */
    public function doDefault(Request $request)
    {
        $this->setPageTitle('Subject Edit');

        $this->institution = $this->getUser()->getInstitution();

        $this->subject = new \App\Db\Subject();
        $this->subject->institutionId = $this->institution->id;
        if ($request->get('subjectId')) {
            $this->subject = \App\Db\SubjectMap::create()->find($request->get('subjectId'));
            if ($this->institution->id != $this->subject->institutionId) {
                throw new \Tk\Exception('You do not have permission to edit this subject.');
            }
        }

        $this->form = \App\Config::getInstance()->createForm('subjectEdit');
        $this->form->setRenderer(\App\Config::getInstance()->createFormRenderer($this->form));

        $this->form->addField(new Field\Input('name'))->setRequired(true);
        $this->form->addField(new Field\Input('code'))->setRequired(true);
        $this->form->addField(new Field\Input('email'))->setRequired(true);
        $this->form->addField(new \App\Form\Field\DateRange('date'))->setRequired(true)->setLabel('Dates')->setNotes('The start and end dates of the subject. Placements cannot be created outside these dates.');
//        $this->form->addField(new Field\Input('dateStart'))->addCss('date')->setRequired(true);
//        $this->form->addField(new Field\Input('dateEnd'))->addCss('date')->setRequired(true);
        $this->form->addField(new Field\Textarea('description'));

        $this->form->addField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->form->addField(new Event\Submit('save', array($this, 'doSubmit')));
        $url = \Uni\Uri::createHomeUrl('/subjectManager.html');
        $this->form->addField(new Event\Link('cancel', $url));

        $this->form->load(\App\Db\SubjectMap::create()->unmapForm($this->subject));
        $this->form->execute();

    }

    /**
     * @param \Tk\Form $form
     */
    public function doSubmit($form)
    {
        // Load the object with data from the form using a helper object
        \App\Db\SubjectMap::create()->mapForm($form->getValues(), $this->subject);

        $form->addFieldErrors($this->subject->validate());

        if ($form->hasErrors()) {
            return;
        }

        $this->subject->save();

        // If this is a staff member add them to the subject
        if ($this->getUser()->hasRole(\App\Db\User::ROLE_STAFF)) {
            \App\Db\SubjectMap::create()->addUser($this->subject->id, $this->getUser()->id);
        }

        \Tk\Alert::addSuccess('Record saved!');

        if ($form->getTriggeredEvent()->getName() == 'update') {
            \Uni\Uri::createHomeUrl('/subjectManager.html')->redirect();
        }

        \Tk\Uri::create()->set('subjectId', $this->subject->id)->redirect();
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        // Render the form
        $template->insertTemplate('form', $this->form->getRenderer()->show());

        if ($this->subject->id && $this->getUser()->isStaff()) {
            $this->getActionPanel()->add(\Tk\Ui\Button::create('Enrollment List',
                \Uni\Uri::createHomeUrl('/subjectEnrollment.html')->set('subjectId', $this->subject->id), 'fa fa-list'));
            $this->getActionPanel()->add(\Tk\Ui\Button::create('Students',
                \Uni\Uri::createHomeUrl('/studentManager.html')->set('subjectId', $this->subject->id), 'fa fa-group'));

            $template->setChoice('update');
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
    <div class="panel-heading"><i class="fa fa-graduation-cap"></i> Subject Edit</div>
    <div class="panel-body">
      <div var="form"></div>
    </div>
  </div>
  
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}