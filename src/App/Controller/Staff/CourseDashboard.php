<?php
namespace App\Controller\Staff;

use Tk\Request;
use Dom\Template;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class CourseDashboard extends \Uni\Controller\Iface
{

    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Course Dashboard');
        if ($this->getUser()->isStaff()) {
            \Uni\Ui\Crumbs::resetCourse($this->getCourse());
        }
    }

    /**
     *
     * @param Request $request
     */
    public function doDefault(Request $request)
    {
        $course = $this->getCourse();
        if ($course) {
            $this->setPageTitle($course->name);
            $this->getTemplate()->insertText('code', $course->code);
        }

        $this->courseUserList = new \App\Ui\CourseUserList($course);

        $this->statusTable = new \App\Ui\Table\StatusPending(\Tk\Uri::create());
        $list = \App\Db\StatusMap::create()->findCurrentStatus(array(
            'profileId' => $course->getProfile()->getId(),
            'courseId' => $course->getId(),
            'name' => 'pending',
        ), $this->statusTable->getTable()->makeDbTool('created DESC'));
        $this->statusTable->setList($list);

    }

    /**
     * @return \App\Ui\CourseUserList
     */
    public function getCourseUserList()
    {
        return $this->courseUserList;
    }

    /**
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $template = parent::show();

        $template->appendTemplate('studentList', $this->courseUserList->show());
        $template->insertTemplate('statusList', $this->statusTable->getTable()->getRenderer()->show());

        $defaultTab = 1;
        if ($defaultTab == 2) {
            $template->addCss('tab2', 'active');
            $template->addCss('tab2-panel', 'active');
        } else {
            $template->addCss('tab1', 'active');
            $template->addCss('tab1-panel', 'active');
        }

        return $template;
    }


    /**
     * DomTemplate magic method
     *
     * @return Template
     * @throws \Dom\Exception
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div>
<style>
/** tabs **/
.panel .nav.nav-tabs li a {
  font-family: 'Oswald', 'Trebuchet MS', 'Open Sans', arial, sans-serif;
  line-height: 1.6;
}
</style>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">Student List</h4>
      <ul class="nav nav-tabs pull-right" style="margin: 0;">
        <li var="tab1"><a href="#tab1" data-toggle="tab">Student List</a></li>
        <li var="tab2"><a href="#tab2" data-toggle="tab">Pending List</a></li>
      </ul>
    </div>
    <div class="panel-body nopadding">
      <div class="tab-content">
        <div class="tab-pane fade in" id="tab1" var="tab1-panel">
          <div class="" var="studentList"></div>
        </div>
        <div class="tab-pane fade in" id="tab2" var="tab2-panel">
          
          <!--     TODO      -->
          
          <div class="statusList clearfix" var="statusList"></div>
          
          <!--     TODO      -->
          
        </div>
      </div>
    </div>
      
  </div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}