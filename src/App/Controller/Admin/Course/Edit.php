<?php
namespace App\Controller\Priv\Course;

use Dom\Template;
use Tk\Form;
use Tk\Form\Event;
use Tk\Form\Field;
use Tk\Request;
use \App\Controller\Admin\Iface;

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
     * @var \Tk\Table
     */
    protected $table = null;


    /**
     *
     */
    public function __construct()
    {
        parent::__construct('Course Edit', array('admin', 'coordinator', 'staff'));
    }

    /**
     *
     * @param Request $request
     * @return \App\Page\Iface|Template|string
     */
    public function doDefault(Request $request)
    {
        $this->course = new \App\Db\Course();

        if ($request->get('courseId')) {
            $this->course = \App\Db\Course::getMapper()->find($request->get('courseId'));
        }

        $this->form = new Form('formEdit');

        $this->form->addField(new Field\Input('name'))->setRequired(true);
        $this->form->addField(new Field\Input('code'))->setRequired(true);
        $this->form->addField(new Field\Input('email'))->setRequired(true);
        $this->form->addField(new Field\Input('start'))->addCss('date')->setRequired(true);
        $this->form->addField(new Field\Input('finish'))->addCss('date')->setRequired(true);
        $this->form->addField(new Field\Checkbox('active'));
        $this->form->addField(new Field\Textarea('description'));

        $this->form->addField(new Event\Button('update', array($this, 'doSubmit')));
        $this->form->addField(new Event\Button('save', array($this, 'doSubmit')));
        //$this->form->addField(new Event\Link('cancel', \Tk\Uri::create($this->getBreadcrumbs()->getBackUrl())));
        $this->form->addField(new Event\Link('cancel', \Tk\Uri::create('admin/courseManager.html')));

        $this->form->load(\App\Db\CourseMap::unmapForm($this->course));
        $this->form->execute();


        // Table of enrolled users
        $this->table = new \Tk\Table('table');
        $this->table->setParam('renderer', \Tk\Table\Renderer\Dom\Table::create($this->table));

        //$this->table->addCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->table->addCell(new \Tk\Table\Cell\Text('name'))->addCellCss('key');
        //$this->table->addCell(new \Tk\Table\Cell\Text('username'));
        $this->table->addCell(new \Tk\Table\Cell\Text('email'));
        //$this->table->addCell(new \Tk\Table\Cell\Text('uid'))->setLabel('UID');
        //$this->table->addCell(new \Tk\Table\Cell\Boolean('active'));
        //$this->table->addCell(new \Tk\Table\Cell\Date('created'))->setFormat(\Tk\Table\Cell\Date::FORMAT_RELATIVE);
        $this->table->addCell(new \Tk\Table\Cell\Date('lastLogin'));

        // Filters
        //$this->table->addFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');

        // Actions
        //$this->table->addAction(\Tk\Table\Action\Button::getInstance('New User', 'fa fa-plus', \Tk\Uri::create('admin/userEdit.html')));
        //$this->table->addAction(\Tk\Table\Action\Delete::getInstance()->setExcludeList(array(1)));
        //$this->table->addAction(\Tk\Table\Action\Csv::getInstance());

        $users = \App\Db\User::getMapper()->findByCourseId($this->course->id, $this->table->makeDbTool('a.id'));
        $this->table->setList($users);
        
        
        
        
        return $this->show();
    }

    /**
     * @return \App\Page\Iface
     */
    public function show()
    {
        $template = $this->getTemplate();

        // Render the form
        $fren = new \Tk\Form\Renderer\Dom($this->form);
        $template->insertTemplate($this->form->getId(), $fren->show()->getTemplate());

        $this->getTemplate()->replaceTemplate('table', $this->table->getParam('renderer')->show());
        
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
        if ($form->getTriggeredEvent()->getName() == 'update')
            \Tk\Uri::create('admin/courseManager.html')->redirect();
            //\Tk\Uri::create($this->getBreadcrumbs()->getBackUrl())->redirect();
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
        <i class="fa fa-university fa-fw"></i>
        Course Edit
      </div>
      <!-- /.panel-heading -->
      <div class="panel-body ">

        <div class="row">
          <div class="col-lg-12">

            <div var="formEdit"></div>

          </div>

        </div>

      </div>
      <!-- /.panel-body -->
    </div>
    <!-- /.panel -->
  </div>

  <div class="col-lg-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="fa fa-users fa-fw"></i>
        Course Enrollments
      </div>
      <!-- /.panel-heading -->
      <div class="panel-body ">

        <div class="row">
          <div class="col-lg-12">

            
            <div var="table"></div>

          </div>

        </div>

      </div>
      <!-- /.panel-body -->
    </div>
    <!-- /.panel -->
  </div>

</div>
XHTML;

        return \Dom\Loader::load($xhtml);
    }

}