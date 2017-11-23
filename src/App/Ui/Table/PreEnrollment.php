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
class PreEnrollment extends \Dom\Renderer\Renderer
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
     * @var \App\Ui\Dialog\PreEnrollment
     */
    protected $dialog = null;


    /**
     * CourseTable constructor.
     * @param \App\Db\Course $course
     * @throws \Tk\Exception
     */
    public function __construct($course)
    {
        $this->course = $course;

        if (!$this->course)
            throw new \Tk\Exception('Invalid course details');

        $this->doDefault();
    }

    /**
     *
     * @return \Dom\Template|Template|string
     */
    public function doDefault()
    {
        $request = \App\Factory::getRequest();

        $this->dialog = new \App\Ui\Dialog\PreEnrollment('Pre-Enroll User');
        $this->dialog->execute($request);


        $this->table = \App\Factory::createTable('pendingUsers');
        $this->table->setRenderer(\App\Factory::createTableRenderer($this->table));
        $this->table->addCss('tk-pending-users');

        $this->table->addCell(new \Tk\Table\Cell\Checkbox('email'));
        $this->table->addCell(new \Tk\Table\Cell\Text('email'))->addCss('key');
        $this->table->addCell(new \Tk\Table\Cell\Text('uid'))->setLabel('UID');
        $this->table->addCell(new EnrolledCell('enrolled'));

        // Actions
        $this->table->addAction(\Tk\Table\Action\Link::create('Add', 'fa fa-plus')->setAttr('data-toggle', 'modal')->setAttr('data-target', '#'.$this->dialog->getId()));
        $this->table->addAction(new ActionUnEnroll('delete', 'email'));
        $this->table->addAction(\Tk\Table\Action\Csv::create());

        // Set Table List
        $filter = $this->table->getFilterValues();
        $filter['institutionId'] = $this->getUser()->getInstitution()->getId();
        $filter['courseId'] = $this->course->getId();

        $list = \App\Db\CourseMap::create()->findPreEnrollments($this->course->getId(), $this->table->makeDbTool('enrolled'));

        $this->table->setList($list);

    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $ren = \Tk\Table\Renderer\Dom\Table::create($this->table);
        $ren->show();
        $this->setTemplate($ren->getTemplate());

        $template = $ren->getTemplate();

        // Enrolment Dialog
        $template->appendTemplate('tk-table', $this->dialog->show());

        $css = <<<CSS
.tk-table .tk-pending-users tr.enrolled td {
  color: #999;
}
CSS;
        $template->appendCss($css);

        return $template;
    }


    /**
     * @return \App\Db\User
     */
    public function getUser()
    {
        return \App\Factory::getConfig()->getUser();
    }

    /**
     * @return \App\Ui\Dialog\PreEnrollment
     */
    public function getDialog()
    {
        return $this->dialog;
    }

}

class EnrolledCell extends \Tk\Table\Cell\Text
{
    /**
     * @param mixed $obj
     * @param int|null $rowIdx The current row being rendered (0-n) If null no rowIdx available.
     * @return string
     */
    public function getCellHtml($obj, $rowIdx = null)
    {
        if (!empty($obj->enrolled)) {
            $this->getRow()->addCss('enrolled');
            $this->getRow()->setAttr('data-user-id', md5($obj->user_id));
            
            $this->setAttr('title', 'User Enrolled');
            $this->setAttr('data-toggle', 'tooltip');
            $this->setAttr('data-placement', 'left');
            $this->addCss('text-center');
            return sprintf('<a href="#" class=""><i class="fa fa-check text-success"></i></a>');
        } else {
            return '';
        }
    }
}

class ActionUnEnroll extends \Tk\Table\Action\Delete
{
    public function execute()
    {
        $request = $this->getTable()->getRequest();
        if (empty($request[$this->checkboxName])) {
            return;
        }
        $selected = $request[$this->checkboxName];
        if (!is_array($selected)) return;
        $i = 0;

        /* @var \stdClass $obj */
        foreach($this->getTable()->getList() as $obj) {
            if (in_array($obj->email, $selected) && !in_array($obj->email, $this->excludeIdList)) {
                $courseMap = \App\Db\CourseMap::create();
                $courseMap->removePreEnrollment($obj->course_id, $obj->email);
                /** @var \App\Db\Course $course */
                $course = $courseMap->find($obj->course_id);
                if ($course) {  // Delete user from course enrolment
                    $user = \App\Db\UserMap::create()->findByEmail($obj->email, $course->institutionId);
                    if ($user) {
                        $courseMap->removeUser($course->getId(), $user->getId());
                    }
                }
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
        return "'Delete ' + selected.length + ' selected records?\\nNote: Enrolled users will be removed from this course'";
    }
}




