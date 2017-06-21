<?php
namespace App\Controller\Course;

use Tk\Request;
use Dom\Template;
use App\Controller\Iface;

/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Enrollment extends Iface
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
     * @var \App\Ui\Dialog\EnrollmentDialog
     */
    protected $dialog = null;
    

    /**
     *
     */
    public function __construct()
    {
        parent::__construct('Enrollment List');
    }

    /**
     *
     * @param Request $request
     * @return \App\Page\Iface|Template|string
     * @throws \Tk\Exception
     */
    public function doDefault(Request $request)
    {
        $this->course = \App\Db\CourseMap::create()->find($request->get('courseId'));
        if (!$this->course)
            throw new \Tk\Exception('Invalid course details');

        $this->dialog = new \App\Ui\Dialog\EnrollmentDialog('Enroll User');
        $this->dialog->execute($request);

        $this->table = new \Tk\Table('tableOne');

        $this->table->addCell(new \Tk\Table\Cell\Checkbox('email'));
        $this->table->addCell(new \Tk\Table\Cell\Text('email'))->addCss('key');
        //$this->table->addCell(new \Tk\Table\Cell\Text('uid'))->setLabel('UID');
        $this->table->addCell(new NameCell('name'));
        //$this->table->addCell(new \Tk\Table\Cell\Date('created'));

        // Actions
        //$this->table->addAction(\Tk\Table\Action\Button::create('New User', 'fa fa-plus', \App\Uri::createHomeUrl('/userEdit.html'));
        //$this->table->addAction(\Tk\Table\Action\Delete::create());
        $this->table->addAction(new ActionUnenroll('unenroll', 'email'));
        $this->table->addAction(\Tk\Table\Action\Csv::create());

        $filter = $this->table->getFilterValues();
        $filter['institutionId'] = $this->getUser()->getInstitution()->id;
        $filter['courseId'] = $this->course->id;

        $list = \App\Db\CourseMap::create()->findEnrollmentByCourseId($this->course->id, $this->table->makeDbTool());
        $this->table->setList($list);

        return $this->show();
    }


    /**
     * @return \App\Page\Iface
     */
    public function show()
    {
        $template = $this->getTemplate();

        // Enrollment Dialog
        $template->insertTemplate('dialog', $this->dialog->show());
        $template->setAttr('modelBtn', 'data-target', '#'.$this->dialog->getId());

        $ren = \Tk\Table\Renderer\Dom\Table::create($this->table);
        $ren->show();
        $template->replaceTemplate('table', $ren->getTemplate());
        $template->insertText('panelTitle', $this->course->code . ' Enrollment List');

        return $this->getPage()->setPageContent($template);
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
      <i class="fa fa-cogs fa-fw"></i> Actions
    </div>
    <div class="panel-body">
      <a href="javascript: window.history.back();" class="btn btn-default"><i class="fa fa-arrow-left"></i>
        <span>Back</span></a>
      <a href="javascript:;" class="btn btn-default" data-toggle="modal" data-target="#" var="modelBtn"><i class="fa fa-user-plus"></i>
        <span>Add Enrollment</span></a>
    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading">
      <i class="fa fa-users fa-fw"></i> <span var="panelTitle">Users</span>
    </div>
    <div class="panel-body">
      <div var="table"></div>
    </div>
  </div>

  <div data-comment="dialog boxes" var="dialog"></div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}


class NameCell extends \Tk\Table\Cell\Text
{
    public function getPropertyValue($obj, $property)
    {
        $course = \App\Db\CourseMap::create()->find($obj->course_id);
        if ($course) {
            $user = \App\Db\UserMap::create()->findByEmail($obj->email, $course->institutionId);
            if ($user) {
                return $user->name;
            }
        }
        return '';
    }
}


class ActionUnenroll extends \Tk\Table\Action\Delete
{

    public function __construct($name = 'unenroll', $checkboxName = 'email', $icon = 'glyphicon glyphicon-remove')
    {
        parent::__construct($name, $icon);
        $this->checkboxName = $checkboxName;
    }


    /**
     * @return mixed
     */
    public function execute()
    {
        $request = $this->getTable()->getRequest();
        if (empty($request[$this->checkboxName])) {
            return;
        }
        $selected = $request[$this->checkboxName];
        if (!is_array($selected)) return;
        $i = 0;

        /** @var \stdClass $obj */
        foreach($this->getTable()->getList() as $obj) {
            if (in_array($obj->email, $selected) && !in_array($obj->email, $this->excludeIdList)) {
                \App\Db\CourseMap::create()->unenrollUser($obj->course_id, $obj->email);
                $i++;
            }
        }

        \Tk\Uri::create()->delete($this->getTable()->makeInstanceKey($this->getName()))->redirect();
    }

    /**
     * @return string
     */
    protected function getJs()
    {
        $btnId = $this->getTable()->makeInstanceKey($this->getName());
        $js = <<<JS
jQuery(function($) {

    var tid = '{$this->getTable()->getId()}';
    var cbName = '{$this->checkboxName}';
    var btnId = '$btnId';

    $('#fid-'+btnId).on('click', function (e) {
        var selected = $('#'+tid+' input[name^=\''+cbName+'\']:checked');
        if (!selected.length) return false;
        if (!confirm('Are you sure you want to un-enroll the ' + selected.length + ' selected students?')) {
            return false;
        }
    });
    function initCb(e) {
        if (e && e.target.name == 'cb_'+cbName+'_all') {
            if ($(e.target).prop('checked')) {
                $('#fid-'+btnId).removeClass('disabled');
            } else {
                $('#fid-'+btnId).addClass('disabled');
            }
            return true;
        }
        if ($('#'+tid+' input[name^=\''+cbName+'\']:checked').length) {
            $('#fid-'+btnId).removeClass('disabled');
        } else {
            $('#fid-'+btnId).addClass('disabled');
        }
    }
    
    $('#'+tid+' input[name^=\''+cbName+'\'], #'+tid+' input[name^=\'cb_'+cbName+'_all\']').on('change', initCb);
    initCb();
    
});
JS;
        return $js;
    }


}




