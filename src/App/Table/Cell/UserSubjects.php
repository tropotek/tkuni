<?php
namespace App\Table\Cell;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
class UserSubjects extends \Tk\Table\Cell\Text
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
     * @throws \Tk\Db\Exception
     */
    public function getPropertyValue($obj, $property)
    {
        //$val =  parent::getPropertyValue($obj, $property);
        $list = \App\Db\SubjectMap::create()->findByUserId($obj->id, $this->institutionId, \Tk\Db\Tool::create('a.name'));
        $val = '';
        /** @var \App\Db\Subject $subject */
        foreach ($list as $subject) {
            $val .= $subject->code . ', ';
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