<?php
namespace App\Table\Cell;


/**
 * Class UserCourses
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
class UserCourses extends \Tk\Table\Cell\Text
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
        $val = '';
        /** @var \App\Db\Course $course */
        foreach ($courseList as $course) {
            $val .= $course->code . ', ';
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