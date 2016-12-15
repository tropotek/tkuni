<?php
namespace App\Db;

use Tk\Db\Data;

/**
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Course extends \Tk\Db\Map\Model
{
    
    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $institutionId = 0;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $code = '';

    /**
     * @var string
     */
    public $email = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var \DateTime
     */
    public $start = null;

    /**
     * @var \DateTime
     */
    public $finish = null;

    /**
     * @var boolean
     */
    public $active = true;

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;

    /**
     * @var \App\Db\Institution
     */
    private $institution = null;

    /**
     * @var Data
     */
    private $data = null;


    /**
     * Course constructor.
     */
    public function __construct()
    {
        $this->modified = \Tk\Date::create();
        $this->created = \Tk\Date::create();
    }

    /**
     *
     */
    public function save()
    {
        $this->getData()->save();
        parent::save();
    }

    /**
     * Get the institution related to this user
     */
    public function getInstitution()
    {
        if (!$this->institution) {
            $this->institution = \App\Db\InstitutionMap::create()->find($this->institutionId);
        }
        return $this->institution;
    }

    /**
     * Get the data object
     *
     * @return \Tk\Db\Data
     */
    public function getData()
    {
        if (!$this->data)
            $this->data = \Tk\Db\Data::create($this->id, get_class($this));
        return $this->data;
    }

    /**
     * 
     * @param $user
     * @return mixed
     */
    public function isUserEnrolled($user)
    {
        return self::getMapper()->inCourse($this->id, $user->id);
    }

    /**
     * Enroll a user in this course
     * 
     * @param $user
     * @return $this
     */
    public function enrollUser($user)
    {
        if (!$this->isUserEnrolled($user)) {
            self::getMapper()->addUserCourse($this->id, $user->id);
        }
        return $this;
    }

    /**
     *
     */
    public function validate()
    {
        $errors = array();

        if ((int)$this->institutionId <= 0) {
            $errors['institutionId'] = 'Invalid Institution ID';
        }
        if (!$this->name) {
            $errors['name'] = 'Please enter a valid course name';
        }
        if (!$this->code) {
            $errors['code'] = 'Please enter a valid course code';
        } else {
            // Look for existing courses with same code
            $c = \App\Db\CourseMap::create()->findByCode($this->code, $this->institutionId);
            if ($c && $c->id != $this->id) {
                $errors['code'] = 'Course code already exists';
            }
        }
        
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }
        
        return $errors;
    }
}