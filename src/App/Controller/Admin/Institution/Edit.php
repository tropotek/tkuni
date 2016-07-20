<?php
namespace App\Controller\Admin\Institution;

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
        parent::__construct('Institution Edit');
    }

    /**
     *
     * @param Request $request
     * @return \App\Page\Iface|Template|string
     */
    public function doDefault(Request $request)
    {
        $this->institution = new \App\Db\Institution();

        if ($request->get('institutionId')) {
            $this->institution = \App\Db\Institution::getMapper()->find($request->get('institutionId'));
        }

        $this->form = new Form('formEdit');

        $this->form->addField(new Field\Input('name'))->setRequired(true);
        $this->form->addField(new Field\Input('email'))->setRequired(true);
        $this->form->addField(new Field\File('logo', $request, $this->getConfig()->getDataPath()))->setAttr('accept', '.png,.jpg,.jpeg,.gif');
        $this->form->addField(new Field\Checkbox('active'));
        $this->form->addField(new Field\Textarea('description'));

        $this->form->addField(new Event\Button('update', array($this, 'doSubmit')));
        $this->form->addField(new Event\Button('save', array($this, 'doSubmit')));
        $this->form->addField(new Event\Link('cancel', \Tk\Uri::create('/admin/institutionManager.html')));


        $this->form->load(\App\Db\InstitutionMap::unmapForm($this->institution));
        $this->form->execute();

        return $this->show();
    }

    /**
     * @return \App\Page\Iface
     */
    public function show()
    {
        $template = $this->getTemplate();

        if ($this->institution->id) {
            $courseTable = new \App\Ui\CourseTable($this->institution->id);
            $template->insertTemplate('courseTable', $courseTable->show());

            $staffTable = new \App\Ui\StaffTable($this->institution->id);
            $template->insertTemplate('staffTable', $staffTable->show());

            $studentTable = new \App\Ui\StudentTable($this->institution->id);
            $template->insertTemplate('studentTable', $studentTable->show());

            $template->addClass('editPanel', 'col-md-3');
            $template->setChoice('showInfo');
        } else {
            $template->addClass('editPanel', 'col-md-12');
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
        \App\Db\InstitutionMap::mapForm($form->getValues(), $this->institution);

        $form->addFieldErrors(\App\Db\InstitutionValidator::create($this->institution)->getErrors());

        $form->getField('logo')->isValid();

        if ($form->hasErrors()) {
            return;
        }

        $rel = '/institution/logo/' . $this->institution->getVolatileId() . '/' . $form->getField('logo')->getUploadedFile()->getFilename();
        $form->getField('logo')->moveTo($rel);
        // Get the relative file path from the field
        $this->institution->logo = $form->getField('logo')->getValue();

        $this->institution->save();

        \App\Alert::addSuccess('Record saved!');
        if ($form->getTriggeredEvent()->getName() == 'update')
            \Tk\Uri::create('admin/institutionManager.html')->redirect();
        \Tk\Uri::create()->set('institutionId', $this->institution->id)->redirect();
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
  <div class="" var="editPanel">
    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="fa fa-university fa-fw"></i> Institution
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
  
  <div class="col-lg-9" choice="showInfo">
    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="fa fa-university fa-fw"></i> Institution
      </div>
      <div class="panel-body ">
        
          <!-- Nav tabs -->
          <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#courses" aria-controls="courses" role="tab" data-toggle="tab">Courses</a></li>
            <li role="presentation"><a href="#staff" aria-controls="staff" role="tab" data-toggle="tab">Staff</a></li>
            <li role="presentation"><a href="#students" aria-controls="students" role="tab" data-toggle="tab">Students</a></li>
          </ul>
        
          <!-- Tab panes -->
          <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="courses">
              <div var="courseTable">Courses ...</div>
            </div>
            <div role="tabpanel" class="tab-pane" id="staff">
              <div var="staffTable">Staff ...</div>
            </div>
            <div role="tabpanel" class="tab-pane" id="students">
              <div var="studentTable">Students ...</div>
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