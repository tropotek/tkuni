<?php
namespace App\Ui;

use Dom\Template;
use Tk\Form;
use Tk\Form\Event;
use Tk\Form\Field;
use Tk\Request;

/**
 * Class CourseTable
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class UserTable extends \Dom\Renderer\Renderer
{

    /**
     * @var \Tk\Table
     */
    protected $table = null;

    /**
     * @var int
     */
    protected $institutionId = 0;

    /**
     * @var int
     */
    protected $courseId = 0;

    /**
     * @var null|array|string
     */
    protected $role = null;

    /**
     * @var null|\Tk\Uri
     */
    protected $editUrl = null;


    /**
     * CourseTable constructor.
     * @param int $institutionId
     * @param null|array|string $role
     * @param int $courseId
     * @param null|\Tk\Uri $editUrl
     */
    public function __construct($institutionId = 0, $role = null, $courseId = 0, $editUrl = null)
    {
        $this->institutionId = $institutionId;
        $this->role = $role;
        $this->courseId = $courseId;
        $this->editUrl = $editUrl;
        $this->doDefault();
    }


    /**
     *
     * @return \App\Page\Iface|Template|string
     */
    public function doDefault()
    {

        $this->table = new \Tk\Table('StaffList');
        $this->table->setParam('renderer', \Tk\Table\Renderer\Dom\Table::create($this->table));

        $this->table->addCell(new \Tk\Table\Cell\Text('name'))->addCss('key')->setUrl($this->editUrl);

        if ($this->institutionId)
            $this->table->addCell(new CourseCell('course', $this->institutionId));

        $this->table->addCell(new \Tk\Table\Cell\Text('email'));

        if (!$this->role)
            $this->table->addCell(new \Tk\Table\Cell\Text('role'));

        $this->table->addCell(new \Tk\Table\Cell\Boolean('active'));
        $this->table->addCell(new \Tk\Table\Cell\Date('created'));

        // Filters
        //$this->table->addFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');

        // Actions
        //$this->table->addAction(\Tk\Table\Action\Csv::getInstance(\App\Factory::getConfig()->getDb()));

        // Set list
        $filter = $this->table->getFilterValues();
        $filter['institutionId'] = $this->institutionId;
        $filter['courseId'] = $this->courseId;
        $filter['role'] = $this->role;

        $users = \App\Db\UserMap::create()->findFiltered($filter, $this->table->makeDbTool('a.name'));
        $this->table->setList($users);

    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $this->table->getParam('renderer')->show();
        $this->setTemplate($this->table->getParam('renderer')->getTemplate());
        return $this->table->getParam('renderer')->getTemplate();
    }

}

class CourseCell extends \Tk\Table\Cell\Text
{

    protected $institutionId = 0;


    public function __construct($property, $institutionId = 0)
    {
        parent::__construct($property);
        $this->institutionId = $institutionId;
        $this->setOrderProperty('');
        $this->setCharacterLimit(120);
    }

    /**
     * @param \App\Db\User $obj
     * @param string $property
     * @return mixed
     */
    public function getPropertyValue($obj, $property)
    {
        //$val =  parent::getPropertyValue($obj, $property);
        $courseList = \App\Db\CourseMap::create()->findByUserId($obj->id, $this->institutionId, \Tk\Db\Tool::create('a.name'));
        $val =  '';
        foreach ($courseList as $course) {
            $val .= $course->code. ', ';
        }
        $val = rtrim($val, ', ');

        return $val;
    }


    /**
     * @param mixed $obj
     * @param null $rowIdx
     * @return string
     */
    public function getCellHtml($obj, $rowIdx = null)
    {
        $propValue = parent::getCellHtml($obj);
        $str = '<small>' . $propValue . '</small>';
        return $str;
    }


}