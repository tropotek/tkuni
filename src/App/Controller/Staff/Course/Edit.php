<?php
namespace App\Controller\Staff\Course;

use Dom\Template;
use Tk\Form;
use Tk\Form\Event;
use Tk\Form\Field;
use Tk\Request;
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
     * @var \App\Db\Course
     */
    private $course = null;

    /**
     * @var \App\Db\Institution
     */
    private $institution = null;

    /**
     * @var \Tk\Table
     */
    protected $table = null;


    /**
     *
     */
    public function __construct()
    {
        parent::__construct('Course Edit');
    }

    /**
     *
     * @param Request $request
     * @return \App\Page\Iface|Template|string
     */
    public function doDefault(Request $request)
    {
        $this->course = new \App\Db\Course();
        $this->course = (int)$request->get('institutionId');

        if ($request->get('courseId')) {
            $this->course = \App\Db\Course::getMapper()->find($request->get('courseId'));
        }
        $this->institution = \App\Db\Institution::getMapper()->find($this->course->institutionId);

        $this->form = new Form('formEdit');
        
        if (!$request->get('institutionId')) {
            $list = \Tk\Form\Field\Option\ArrayObjectIterator::create(\App\Db\Institution::getMapper()->findActive(\Tk\Db\Tool::create())->toArray());
            $this->form->addField(new Field\Select('institutionId', $list))->setRequired(true)->prependOption('-- Select --', '');
        }
        
        $this->form->addField(new Field\Input('name'))->setRequired(true);
        $this->form->addField(new Field\Input('code'))->setRequired(true);
        $this->form->addField(new Field\Input('email'))->setRequired(true);
        $this->form->addField(new Field\Input('start'))->addCss('date')->setRequired(true);
        $this->form->addField(new Field\Input('finish'))->addCss('date')->setRequired(true);
        $this->form->addField(new Field\Textarea('description'));

        $this->form->addField(new Event\Button('update', array($this, 'doSubmit')));
        $this->form->addField(new Event\Button('save', array($this, 'doSubmit')));
        $url = \Tk\Uri::create('/staff/courseManager.html');
        $this->form->addField(new Event\Link('cancel', $url));

        $this->form->load(\App\Db\CourseMap::unmapForm($this->course));
        $this->form->execute();

        return $this->show();
    }

    /**
     * @return \App\Page\Iface
     */
    public function show()
    {
        $template = $this->getTemplate();

        if ($this->course->id) {
            $staffTable = new \App\Ui\UserTable(0, null, $this->course->id);
            $template->insertTemplate('table', $staffTable->show());
        }

        // Render the form
        $fren = new \Tk\Form\Renderer\Dom($this->form);
        $template->insertTemplate($this->form->getId(), $fren->show()->getTemplate());

        return $this->getPage()->setPageContent($template);
    }

    /**
     * @param \Tk\Form $form
     */
    public function doSubmit($form)
    {
        // Load the object with data from the form using a helper object
        //\App\Form\ModelLoader::loadObject($form, $this->user);
        \App\Db\CourseMap::mapForm($form->getValues(), $this->course);

        $form->addFieldErrors(\App\Db\CourseValidator::create($this->course)->getErrors());

        if ($form->hasErrors()) {
            return;
        }

        $this->course->save();

        \App\Alert::addSuccess('Record saved!');

        if ($form->getTriggeredEvent()->getName() == 'update') {
            \Tk\Uri::create('staff/courseManager.html')->redirect();
        }

        \Tk\Uri::create()->set('courseId', $this->course->id)->redirect();
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
        <i class="fa fa-university fa-fw"></i> Course Edit
      </div>
      <div class="panel-body ">
        <div class="row">
          <div class="col-lg-12">
            <div var="formEdit"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="fa fa-users fa-fw"></i> Course Enrollments
      </div>
      <div class="panel-body ">
        
        <!-- div class="row">
          <div class="col-lg-12">
            <a href="javascript:;" class="btn btn-default"><i class="fa fa-users"></i> <span>Enroll User</span></a>
          </div>
        </div -->
        
        <div class="row">
          <div class="col-lg-12">
            <div var="table"></div>
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