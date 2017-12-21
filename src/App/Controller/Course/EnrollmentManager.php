<?php
namespace App\Controller\Course;

use Tk\Request;
use Dom\Template;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class EnrollmentManager extends \Uni\Controller\AdminIface
{

    /**
     * @var \Tk\Table
     */
    protected $table = null;

    /**
     * @var \App\Db\Course
     */
    protected $course = null;

    /**
     * @var \App\Ui\Table\PreEnrollment
     */
    protected $pendingTable = null;

    /**
     * @var \App\Ui\Table\Enrolled
     */
    protected $enrolledTable = null;

    /**
     * @var \App\Ui\Dialog\FindUser
     */
    protected $userDialog = null;
    

    /**
     *
     * @param Request $request
     * @throws \Tk\Exception
     */
    public function doDefault(Request $request)
    {
        $this->course = \App\Db\CourseMap::create()->find($request->get('courseId'));
        if (!$this->course)
            throw new \Tk\Exception('Invalid course details');
        
        $this->setPageTitle("`" . $this->course->name . '` Enrolments');

        $this->enrolledTable = new \App\Ui\Table\Enrolled($this->course);
        $this->pendingTable = new \App\Ui\Table\PreEnrollment($this->course);


        $filter = array();
        $filter['institutionId'] = $this->course->institutionId;
        $filter['active'] = '1';
        $filter['role'] = \App\Db\User::ROLE_STUDENT;
        $this->userDialog = new \App\Ui\Dialog\FindUser('Enrol Student', $filter);
        $this->userDialog->setOnSelect(function ($dialog, $data) {
            /** @var \App\Db\User $user */
            $user = \App\Db\UserMap::create()->findByHash($data['userHash'], $this->course->institutionId);
            if (!$user || !$user->hasRole(array(\App\Db\User::ROLE_STUDENT))) {
                \Tk\Alert::addWarning('Invalid user.');
            } else {
                if (!$user->isEnrolled($this->course->getId())) {
                    // TODO: test for any preconditions, maybe fire an enrollment event?
                    \App\Db\CourseMap::create()->addUser($this->course->getId(), $user->getId());
                    \Tk\Alert::addSuccess($user->getDisplayName() . ' added to the course ' . $this->course->name);
                } else {
                    \Tk\Alert::addWarning($user->getDisplayName() . ' already enrolled in the course ' . $this->course->name);
                }
            }
        });
        $this->userDialog->execute($request);

    }


    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        // Enrolment Dialog
        $template->appendTemplate('enrollment', $this->userDialog->show());
        //$template->setAttr('addUser', 'data-target', '#'.$this->userDialog->getId());
        $this->getActionPanel()->addButton(\Tk\Ui\Button::create('Enroll Student','#', 'fa fa-user-plus'))
            ->setAttr('data-toggle', 'modal')->setAttr('data-target', '#'.$this->userDialog->getId())
            ->setAttr('title', 'Add an existing student to this course');

        // Enrolled Table
        $template->replaceTemplate('enrolledTable', $this->enrolledTable->show());
        
        // Pending Table
        $template->replaceTemplate('pendingTable', $this->pendingTable->show());
        //$template->setAttr('modelBtn', 'data-target', '#'.$this->pendingTable->getDialog()->getId());
        $this->getActionPanel()->addButton(\Tk\Ui\Button::create('Pre-Enroll Student','#', 'fa fa-user-plus'))
            ->setAttr('data-toggle', 'modal')->setAttr('data-target', '#'.$this->pendingTable->getDialog()->getId())
            ->setAttr('title', 'Pre-Enroll a non-existing student, they will automatically be enrolled on login');
        
        $js = <<<JS
jQuery(function($) {
  
  $('tr[data-user-id]').hover(
    function(e) {
      var userId = $(this).attr('data-user-id');
      $('tr[data-user-id="'+userId+'"]').addClass('tk-hover');
    },
    function(e) {
      var userId = $(this).attr('data-user-id');
      $('tr[data-user-id="'+userId+'"]').removeClass('tk-hover');
    }
  );
  
});
JS;
        $template->appendJs($js);

        $css = <<<CSS
.tk-table tr.tk-hover td {
  background-color: #7796b4;
  color: #efefef !important;
}
CSS;
        $template->appendCss($css);





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
<div var="enrollment">

  <div class="row">
    <div class="col-md-8">

      <div class="panel panel-default">
        <div class="panel-heading"><i class="fa fa-users"></i> <span var="">Enrolled</span></div>
        <div class="panel-body">
          <div var="enrolledTable"></div>
        </div>
      </div>

    </div>
    <div class="col-md-4">

      <div class="panel panel-default">
        <div class="panel-heading"><i class="fa fa-users"></i> <span>Pending</span></div>
        <div class="panel-body">
          <div var="pendingTable"></div>
          <div class="small">
            <p>
              - Pre-enrolled users will automatically be enrolled into this course on their next login.<br/>
              - Deleting an enrolled user from this list will also delete them from the pre-enrollment list.
            </p>
          </div>
        </div>
      </div>

    </div>
  </div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }
    
}







