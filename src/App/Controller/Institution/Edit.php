<?php
namespace App\Controller\Institution;

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
class Edit extends \App\Controller\AdminIface
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
     * @var \App\Db\user
     */
    private $owner = null;

    /**
     * @var \Tk\Table
     */
    protected $table = null;


    /**
     * @param Request $request
     * @throws \Tk\Exception
     */
    public function doDefault(Request $request)
    {
        $this->setPageTitle('Institution Edit');

        $this->institution = new \App\Db\Institution();
        $this->owner = new \App\Db\User();

        if ($request->get('institutionId')) {
            $this->institution = \App\Db\InstitutionMap::create()->find($request->get('institutionId'));
            $this->owner = $this->institution->getOwnerUser();
        }
        if ($this->getUser()->isClient()) {
            $this->institution = \App\Db\InstitutionMap::create()->findByOwnerId($this->getuser()->getId());
            $this->owner = $this->institution->getOwnerUser();
        }

        $this->form = \App\Factory::createForm('institutionEdit');
        $this->form->setRenderer(\App\Factory::createFormRenderer($this->form));

        $this->form->addField(new Field\Input('name'))->setRequired(true)->setTabGroup('Details');
        $this->form->addField(new Field\Input('username'))->setRequired(true)->setTabGroup('Details');
        $this->form->addField(new Field\Input('email'))->setRequired(true)->setTabGroup('Details');
        $this->form->addField(new Field\File('logo', $this->institution->getDataPath().'/logo/'))
            ->setAttr('accept', '.png,.jpg,.jpeg,.gif')->setTabGroup('Details')->addCss('tk-imageinput');

        $insUrl = \Tk\Uri::create('/inst/'.$this->institution->getHash().'/login.html');
        if ($this->institution->domain)
            $insUrl = \Tk\Uri::create('/login.html')->setHost($this->institution->domain);
        $insUrl = $insUrl->setScheme('https')->toString();
        $this->form->addField(new Field\Input('domain'))->setTabGroup('Details')->setNotes('Your Institution login URL is: <a href="'.$insUrl.'">'.$insUrl.'</a>' );
        $this->form->addField(new Field\Textarea('description'))->setTabGroup('Details');
        $this->form->addField(new Field\Checkbox('active'))->setTabGroup('Details');

        $this->form->setAttr('autocomplete', 'off');
        $f = $this->form->addField(new Field\Password('newPassword'))->setAttr('placeholder', 'Click to edit')->setAttr('readonly', 'true')->setAttr('onfocus', "this.removeAttribute('readonly');this.removeAttribute('placeholder');")->setTabGroup('Password');
        if (!$this->owner->getId())
            $f->setRequired(true);
        $f = $this->form->addField(new Field\Password('confPassword'))->setAttr('placeholder', 'Click to edit')->setAttr('readonly', 'true')->setAttr('onfocus', "this.removeAttribute('readonly');this.removeAttribute('placeholder');")->setNotes('Change this users password.')->setTabGroup('Password');
        if (!$this->owner->getId())
            $f->setRequired(true);

        $this->form->addField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->form->addField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->form->addField(new Event\Link('cancel', \Tk\Uri::create('/admin/institutionManager.html')));

        $this->form->load(\App\Db\InstitutionMap::create()->unmapForm($this->institution));
        $this->form->load(\App\Db\UserMap::create()->unmapForm($this->owner));
        $this->form->load($this->institution->getData()->all());

        $this->form->execute();

    }

    /**
     * @param \Tk\Form $form
     * @throws Form\Exception
     * @throws \Tk\Exception
     */
    public function doSubmit($form)
    {
        // Load the object with data from the form using a helper object
        \App\Db\InstitutionMap::create()->mapForm($form->getValues(), $this->institution);
        \App\Db\UserMap::create()->mapForm($form->getValues(), $this->owner);
        $data = $this->institution->getData();
        $data->replace($form->getValues('/^(inst)/'));

        $form->addFieldErrors($this->institution->validate());
        $form->addFieldErrors($this->owner->validate());

        /** @var \Tk\Form\Field\File $logo */
        $logo = $form->getField('logo');
        if ($logo->hasFile() && !preg_match('/\.(gif|jpe?g|png)$/i', $logo->getValue())) {
            $form->addFieldError('logo', 'Please Select a valid image file. (jpg, png, gif only)');
        }

        // Password validation needs to be here
        if ($this->form->getFieldValue('newPassword')) {
            if ($this->form->getFieldValue('newPassword') != $this->form->getFieldValue('confPassword')) {
                $form->addFieldError('newPassword', 'Passwords do not match.');
                $form->addFieldError('confPassword');
            }
        }
        if (!$this->owner->id && !$this->form->getFieldValue('newPassword')) {
            $form->addFieldError('newPassword', 'Please enter a new password.');
        }


        if ($form->hasErrors()) {
            return;
        }

        $logo->saveFile();
        // resize the image if needed
        if ($logo->hasFile()) {
            $fullPath = $this->getConfig()->getDataPath() . $this->institution->logo;
            \Tk\Image::create($fullPath)->bestFit(256, 256)->save();
        }

        // Hash the password correctly
        if ($this->form->getFieldValue('newPassword')) {
            $pwd = \App\Db\User::createPassword();
            $this->owner->setNewPassword($pwd);
        }

        $this->owner->save();
        $this->institution->ownerId = $this->owner->id;
        $this->institution->save();

        \Tk\Alert::addSuccess('Record saved!');
        if ($form->getTriggeredEvent()->getName() == 'update')
            \Tk\Uri::create('admin/institutionManager.html')->redirect();
        \Tk\Uri::create()->set('institutionId', $this->institution->id)->redirect();
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        // Render the form
        $template->insertTemplate('form', $this->form->getRenderer()->show());

        if ($this->institution->id) {

            if (!$this->getUser()->isClient()) {
//                $courseTable = new \App\Ui\Table\Course($this->institution->id);
//                $template->insertTemplate('courseTable', $courseTable->show());

                $staffTable = new \App\Ui\Table\User($this->institution->id, \App\Db\User::ROLE_STAFF, 0);
                $template->insertTemplate('staffTable', $staffTable->show());

//                $studentTable = new \App\Ui\Table\User($this->institution->id, \App\Db\User::ROLE_STUDENT, 0);
//                $template->insertTemplate('studentTable', $studentTable->show());

                $template->addCss('editPanel', 'col-md-5');
                $template->setChoice('showInfo');
            } else {
                $template->addCss('editPanel', 'col-md-12');
            }
            $template->setChoice('update');

            if (\App\Listener\MasqueradeHandler::canMasqueradeAs($this->getUser(), $this->institution->getOwnerUser())) {
                $this->getActionPanel()->addButton(\Tk\Ui\Button::create('Masquerade',
                    \App\Uri::create()->reset()->set(\App\Listener\MasqueradeHandler::MSQ, $this->institution->getOwnerUser()->hash), 'fa fa-user-secret'))->addCss('tk-masquerade');
            }
            $this->getActionPanel()->addButton(\Tk\Ui\Button::create('Plugins',
                \App\Uri::createHomeUrl('/institution/'.$this->institution->getId().'/plugins.html'), 'fa fa-plug'));
            $this->getActionPanel()->addButton(\Tk\Ui\Button::create('Skills Setup',
                \App\Uri::createHomeUrl('/skillSetup.html')->set('institutionId', $this->institution->id), 'fa fa-medkit'));

        } else {
            $template->addCss('editPanel', 'col-md-12');
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
<div>

  <div class="row">
    <div class="" var="editPanel">
      <div class="panel panel-default">
        <div class="panel-heading"><i class="fa fa-university fa-fw"></i> Institution</div>
        <div class="panel-body">
          <div var="form"></div>
        </div>
      </div>
    </div>

    <div class="col-md-7" choice="showInfo">
      <div class="panel panel-default">
        <div class="panel-heading"><i class="fa fa-users fa-fw"></i> Staff</div>
        <div class="panel-body">
          <div var="staffTable">Staff ...</div>

          <!-- Nav tabs -->
          <!--<ul class="nav nav-tabs" role="tablist">-->
            <!--<li role="presentation" class="active"><a href="#staff" aria-controls="staff" role="tab" data-toggle="tab">Staff</a></li>-->
            <!--<li role="presentation" class=""><a href="#students" aria-controls="students" role="tab" data-toggle="tab">Students</a></li>-->
            <!--<li role="presentation" class=""><a href="#courses" aria-controls="courses" role="tab" data-toggle="tab">Courses</a></li>-->
          <!--</ul>-->
          <!--<div class="tab-content">-->
            <!--<div role="tabpanel" class="tab-pane active" id="staff">-->
              <!--<div var="staffTable">Staff ...</div>-->
            <!--</div>-->
            <!--<div role="tabpanel" class="tab-pane " id="students">-->
              <!--<div var="studentTable">Students ...</div>-->
            <!--</div>-->
            <!--<div role="tabpanel" class="tab-pane " id="courses">-->
              <!--<div var="courseTable">Courses ...</div>-->
            <!--</div>-->
          <!--</div>-->

        </div>
      </div>
    </div>
  </div>
  
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}