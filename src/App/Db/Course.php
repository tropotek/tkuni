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
     * @var string
     */
    public $lti_consumer_key = '';

    /**
     * @var string
     */
    public $lti_context_id = '';

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
     *
     */
    public function __construct()
    {
        $this->modified = new \DateTime();
        $this->created = new \DateTime();
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
    
    
}

class CourseValidator extends \App\Helper\Validator
{

    /**
     * Implement the validating rules to apply.
     *
     */
    protected function validate()
    {
        /** @var Course $obj */
        $obj = $this->getObject();

        if (!$obj->name) {
            $this->addError('name', 'Invalid field value.');
        }
        if (!$obj->code) {
            $this->addError('code', 'Invalid field value.');
        } else {
            // Look for existing courses with same code
            $c = \App\Db\Course::getMapper()->findByCode($obj->code);
            if ($c && $c->id != $obj->id) {
                $this->addError('code', 'Code already exists.');
            }
        }
        
        if (!filter_var($obj->email, FILTER_VALIDATE_EMAIL)) {
            $this->addError('email', 'Please enter a valid email address');
        }
        
        // TODO: Validate start and end dates
        
        
    }
}
