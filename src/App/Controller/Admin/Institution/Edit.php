<?php
namespace App\Controller\Admin\Institution;

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
//        $iid = 0;
//        if ($this->institution) {
//            $iid = $this->institution->id;
//        }
        $clients = new \Tk\Form\Field\Option\ArrayObjectIterator(\App\Db\User::getMapper()->findByRole(\App\Auth\Acl::ROLE_CLIENT)->toArray());
        $this->form->addField(new Field\Select('ownerId', $clients))->prependOption('-- Select --', '')->setRequired(true)->setTabGroup('Details');

        $this->form->addField(new Field\Input('name'))->setRequired(true)->setTabGroup('Details');
        $this->form->addField(new Field\Input('email'))->setRequired(true)->setTabGroup('Details');
        $this->form->addField(new Field\File('logo', $request, $this->getConfig()->getDataPath()))->setAttr('accept', '.png,.jpg,.jpeg,.gif')->setTabGroup('Details');
        $insUrl = \Tk\Uri::create('/inst/'.$this->institution->getHash().'/login.html');
        $this->form->addField(new Field\Input('domain'))->setTabGroup('Details')->setNotes('If this is set the login Url will be http://{domain}/login.html or else use the standard institution login of ' . $insUrl . '');
        $this->form->addField(new Field\Textarea('description'))->setTabGroup('Details');
        $this->form->addField(new Field\Checkbox('active'))->setTabGroup('Details');

        // TODO: Implement LTI tables for LMS access
        $this->form->addField(new Field\Input('ltiKey'))->setTabGroup('LTI');
        $this->form->addField(new Field\Input('ltiSecret'))->setTabGroup('LTI');

        $this->form->addField(new Field\Input('ldapHost'))->setTabGroup('LDAP');
        $this->form->addField(new Field\Input('ldapPort'))->setTabGroup('LDAP');
        $this->form->addField(new Field\Input('ldapBaseDn'))->setTabGroup('LDAP');
        $this->form->addField(new Field\Input('ldapFilter'))->setTabGroup('LDAP');
        $this->form->addField(new Field\Checkbox('ldapTls'))->setTabGroup('LDAP');


        $this->form->addField(new Event\Button('update', array($this, 'doSubmit')));
        $this->form->addField(new Event\Button('save', array($this, 'doSubmit')));
        $this->form->addField(new Event\Link('cancel', \Tk\Uri::create('/admin/institutionManager.html')));

        $this->form->load(\App\Db\InstitutionMap::unmapForm($this->institution));
        $this->form->load($this->institution->getData()->all());

        if ($this->institution->id && $this->institution->getOwner()) {
            $this->form->setFieldValue('ownerId', $this->institution->getOwner()->id);
        }
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
            $courseTable = new \App\Ui\CourseTable($this->institution->id, \Tk\Uri::create('/admin/courseEdit.html')->set('institutionId', $this->institution->id));
            $template->insertTemplate('courseTable', $courseTable->show());

            $staffTable = new \App\Ui\UserTable($this->institution->id, \App\Auth\Acl::ROLE_STAFF, 0, \Tk\Uri::create('/admin/userEdit.html')->set('institutionId', $this->institution->id));
            $template->insertTemplate('staffTable', $staffTable->show());

            $studentTable = new \App\Ui\UserTable($this->institution->id, \App\Auth\Acl::ROLE_STUDENT, 0, \Tk\Uri::create('/admin/userEdit.html')->set('institutionId', $this->institution->id));
            $template->insertTemplate('studentTable', $studentTable->show());

            $template->addClass('editPanel', 'col-md-4');
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

        $data = $this->institution->getData();
        $data->replace($form->getValues('/^ldap/'));
        $data->replace($form->getValues('/^lti/'));
        $data->save();

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
  
  <div class="col-lg-8" choice="showInfo">
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