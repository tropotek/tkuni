<?php
namespace App\Ui\Table;

use Dom\Template;

/**
 * Class CourseTable
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Enrolled extends \Dom\Renderer\Renderer
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
     * @var null|\Tk\Uri
     */
    protected $editUrl = null;


    /**
     * CourseTable constructor.
     *
     * @param \App\Db\Course $course
     * @param null|\Tk\Uri $editUrl
     */
    public function __construct($course, $editUrl = null)
    {
        $this->course = $course;
        $this->editUrl = $editUrl;
        $this->doDefault();
    }
    
    /**
     *
     * @return \App\Page\Iface|Template|string
     */
    public function doDefault()
    {
        $request = \App\Factory::getRequest();

        $this->table = \App\Factory::createTable('enrolledUsers');
        $this->table->setParam('renderer', \App\Factory::createTableRenderer($this->table));
        $this->table->addCss('tk-enrolled-users');

        $this->table->addCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->table->addCell(new ActionsCell($this->course));
        $this->table->addCell(new NameCell('name'))->addCss('key')->setUrl($this->editUrl);
        $this->table->addCell(new \Tk\Table\Cell\Text('email'));
        $this->table->addCell(new \Tk\Table\Cell\Text('role'));
        $this->table->addCell(new \Tk\Table\Cell\Boolean('active'));
        $this->table->addCell(new \Tk\Table\Cell\Date('created'));

        // Actions
        $this->table->addAction(new DeleteUser())->setCourse($this->course);
        $this->table->addAction(\Tk\Table\Action\Csv::create());

        // Set list
        $filter = $this->table->getFilterValues();
        $filter['institutionId'] = $this->course->institutionId;
        $filter['courseId'] = $this->course->getId();
        $filter['role'] = array(\App\Db\User::ROLE_STAFF, \App\Db\User::ROLE_STUDENT);

        $users = \App\Db\UserMap::create()->findFiltered($filter, $this->table->makeDbTool('a.name'));
        $this->table->setList($users);

    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $ren = $this->table->getParam('renderer');
        $ren->show();
        
        /** @var \Dom\Template $template */
        $template = $ren->getTemplate();
        $this->setTemplate($template);
        
        return $template;
    }

}

class ActionsCell extends \Tk\Table\Cell\Text
{

    /**
     * @var \App\Db\Course
     */
    protected $course = null;

    /**
     * @var \App\Ui\Dialog\AjaxSelect
     */
    protected $findCourseDialog = null;

    /**
     * @param \App\Db\Course $course
     */
    public function __construct($course)
    {
        parent::__construct('actions');
        $this->setOrderProperty('');
        $this->course = $course;
        
        //$list = \Tk\Form\Field\Option\ArrayObjectIterator::create(\App\Db\CourseMap::create()->findActive($this->course->institutionId));
        // TODO: remove this class from the list
        $this->findCourseDialog = new \App\Ui\Dialog\AjaxSelect('Migrate Student', array($this, 'onSelect'), \Tk\Uri::create('/ajax/course/findFiltered.html'));
        $this->findCourseDialog->setAjaxParams(array('ignoreUser' => '1', 'courseId' => $this->course->getId()));
        $this->findCourseDialog->setNotes('Select the course to migrate the student to...');
        $this->findCourseDialog->execute(\App\Factory::getRequest());
    }
    
    public function setTable($table)
    {

        $ren = $table->getParam('renderer');
        if ($ren) {
            /** @var \Dom\Template $tableTemplate */
            $tableTemplate = $ren->getTemplate();
            $tableTemplate->appendTemplate('tk-table', $this->findCourseDialog->show());
        }
        return parent::setTable($table);
    }

    public function onSelect($data)
    {
        $dispatcher = \App\Factory::getEventDispatcher();
        // Migrate the user to the new course
        $event = new \Tk\Event\Event();
        $event->set('courseFromId', $this->course->getId());
        $event->set('courseToId', $data['selectedId']);
        $event->set('userId', $data['userId']);
        $dispatcher->dispatch(\App\AppEvents::COURSE_MIGRATE_USER, $event);
        
        if (!$event->isPropagationStopped()) {
            /** @var \App\Db\User $user */
            $user = \App\Db\UserMap::create()->find($event->get('userId'));
            if ($user) {
                if (\App\Db\CourseMap::create()->hasUser($event->get('courseFromId'), $user->getId())) {
                    \App\Db\CourseMap::create()->removeUser($event->get('courseFromId'), $user->getId());
                    // delete user from the pre-enrolment list if exists
                    \App\Db\CourseMap::create()->removePreEnrollment($event->get('courseFromId'), $user->email);
                }
                if (!\App\Db\CourseMap::create()->hasUser($event->get('courseToId'), $user->getId())) {
                    \App\Db\CourseMap::create()->addUser($event->get('courseToId'), $user->getId());
                }
            }
        }
        
        \Tk\Uri::create()->reset()->set('courseId', $this->course->getId())->redirect();
    }

    /**
     * @param \App\Db\User $obj
     * @param int|null $rowIdx The current row being rendered (0-n) If null no rowIdx available.
     * @return string|\Dom\Template
     */
    public function getCellHtml($obj, $rowIdx = null)
    {
        $template = $this->__makeTemplate();

        // exclude any courses already enrolled in
        $enrolledList  = \App\Db\CourseMap::create()->findFiltered(array('userId' => $obj->getId()));
        $exclude = array($this->course->getId());
        foreach ($enrolledList as $course) {
            $exclude[] = $course->getId();
        }
        $list = \App\Db\CourseMap::create()->findFiltered(array(
            'exclude' => $exclude
        ));

        if (count($list) && $obj->isStudent()) {
            $template->setAttr('migrate', 'data-target', '#' . $this->findCourseDialog->getId());
            $template->setAttr('migrate', 'data-toggle', 'modal');
            $template->setAttr('migrate', 'data-user-id', $obj->getId());
            $template->setChoice('migrate');
        }

        return $template;
    }

    /**
     * makeTemplate
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $html = <<<HTML
<div class="text-right">
  <a href="#" class="btn btn-default btn-xs migrateUser" title="Migrate user to another course " var="migrate" choice="migrate"><i class="fa fa-exchange"></i></a>
</div>
HTML;
        return \Dom\Loader::load($html);
    }

}

class NameCell extends \Tk\Table\Cell\Text
{
    public function getCellHtml($obj, $rowIdx = null)
    {
        $this->getRow()->setAttr('data-user-id', md5($obj->getId()) );
        return parent::getCellHtml($obj, $rowIdx);
    }
}

class DeleteUser extends \Tk\Table\Action\Delete
{
    /** @var \App\Db\Course null */
    private $course = null;

    /**
     * @param \App\Db\Course  $course
     * @return $this
     */
    public function setCourse($course)
    {
        $this->course = $course;
        return $this;
    }
    
    public function execute()
    {
        $request = $this->getTable()->getRequest();
        if (empty($request[$this->checkboxName])) {
            return;
        }
        $selected = $request[$this->checkboxName];
        if (!is_array($selected)) return;
        $i = 0;

        $courseId = $this->course->getId();
        /* @var \app\Db\User $obj */
        foreach($this->getTable()->getList() as $obj) {
            if (in_array($obj->getId(), $selected) && !in_array($obj->getId(), $this->excludeIdList)) {
                $courseMap = \App\Db\CourseMap::create();
                $courseMap->removePreEnrollment($courseId, $obj->email);
                $courseMap->removeUser($courseId, $obj->getId());
                $i++;
            }
        }
        \Tk\Uri::create()->remove($this->getTable()->makeInstanceKey($this->getName()))->redirect();
    }

    /**
     * @return string
     */
    protected function getConfirmStr()
    {
        return "'Delete ' + selected.length + ' selected records?\\nNote: Users will be removed from this course and the pending-enrolment list.'";
    }
}