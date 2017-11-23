<?php
namespace App\Controller\Course;

use Dom\Template;
use Tk\Form;
use Tk\Form\Event;
use Tk\Form\Field;
use Tk\Request;
use App\Controller\Iface;

/**
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
     * @var \App\Db\Course
     */
    private $course = null;

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
        $this->setPageTitle('Course Edit');
        
        $this->institution = $this->getUser()->getInstitution();

        $this->course = new \App\Db\Course();
        $this->course->institutionId = $this->institution->id;
        if ($request->get('courseId')) {
            $this->course = \App\Db\CourseMap::create()->find($request->get('courseId'));
            if ($this->institution->id != $this->course->institutionId) {
                throw new \Tk\Exception('You do not have permission to edit this course.');
            }
        }

        $this->form = \App\Factory::createForm('courseEdit');
        $this->form->setRenderer(\App\Factory::createFormRenderer($this->form));

        $this->form->addField(new Field\Input('name'))->setRequired(true);
        $this->form->addField(new Field\Input('code'))->setRequired(true);
        $this->form->addField(new Field\Input('email'))->setRequired(true);
        $this->form->addField(new \App\Form\Field\DateRange('date'))->setRequired(true)->setLabel('Dates')->setNotes('The start and end dates of the course. Placements cannot be created outside these dates.');
//        $this->form->addField(new Field\Input('dateStart'))->addCss('date')->setRequired(true);
//        $this->form->addField(new Field\Input('dateEnd'))->addCss('date')->setRequired(true);
        $this->form->addField(new Field\Textarea('description'));

        $this->form->addField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->form->addField(new Event\Submit('save', array($this, 'doSubmit')));
        $url = \App\Uri::createHomeUrl('/courseManager.html');
        $this->form->addField(new Event\Link('cancel', $url));

        $this->form->load(\App\Db\CourseMap::create()->unmapForm($this->course));
        $this->form->execute();

    }

    /**
     * @param \Tk\Form $form
     */
    public function doSubmit($form)
    {
        // Load the object with data from the form using a helper object
        \App\Db\CourseMap::create()->mapForm($form->getValues(), $this->course);

        $form->addFieldErrors($this->course->validate());

        if ($form->hasErrors()) {
            return;
        }

        $this->course->save();

        // If this is a staff member add them to the course
        if ($this->getUser()->hasRole(\App\Db\User::ROLE_STAFF)) {
            \App\Db\CourseMap::create()->addUser($this->course->id, $this->getUser()->id);
        }

        \Tk\Alert::addSuccess('Record saved!');

        if ($form->getTriggeredEvent()->getName() == 'update') {
            \App\Uri::createHomeUrl('/courseManager.html')->redirect();
        }

        \Tk\Uri::create()->set('courseId', $this->course->id)->redirect();
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        // Render the form
        $template->insertTemplate('form', $this->form->getRenderer()->show());

        if ($this->course->id) {
            $template->setChoice('update');
            $template->setAttr('enroll', 'href', \App\Uri::createHomeUrl('/courseEnrollment.html')->set('courseId', $this->course->id));
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
    <div class="panel-heading">
      <i class="fa fa-cogs"></i> Actions
    </div>
    <div class="panel-body">
      <a href="javascript: window.history.back();" class="btn btn-default"><i class="fa fa-arrow-left"></i> <span>Back</span></a>
      <a href="javascript:;" class="btn btn-default" var="enroll" choice="update"><i class="fa fa-list"></i> <span>Enrollment List</span></a>
    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading">
      <i class="fa fa-graduation-cap"></i> Course Edit
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