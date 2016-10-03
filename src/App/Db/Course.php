<?php
namespace App\Db;


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
     * Course constructor.
     */
    public function __construct()
    {
        $this->modified = \Tk\Date::create();
        $this->created = \Tk\Date::create();
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
            $this->addError('email', 'Please enter a valid email address');
        }
        
        return $errors;
    }
}