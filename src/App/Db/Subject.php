<?php
namespace App\Db;

use Tk\Db\Data;

/**
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Subject extends \Tk\Db\Map\Model implements \Uni\Db\SubjectIface
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
    public $dateStart = null;

    /**
     * @var \DateTime
     */
    public $dateEnd = null;

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
     * @throws \Tk\Db\Exception
     */
    public function getData()
    {
        if (!$this->data)
            $this->data = \Tk\Db\Data::create($this->id, get_class($this));
        return $this->data;
    }

    /**
     * Get the path for all file associated to this object
     *
     * @return string
     * @throws \Tk\Db\Exception
     */
    public function getDataPath()
    {
        return sprintf('%s/subject/%s', $this->getInstitution()->getDataPath(), $this->getVolatileId());
    }

    /**
     *
     * @param $user
     * @return mixed
     */
    public function isUserEnrolled($user)
    {
        return SubjectMap::create()->hasUser($this->id, $user->id);
    }

    /**
     * Enroll a user
     *
     * @param $user
     * @return $this
     */
    public function enrollUser($user)
    {
        if (!$this->isUserEnrolled($user)) {
            SubjectMap::create()->addUser($this->id, $user->id);
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getInstitutionId()
    {
        return $this->institutionId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return \DateTime
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * @return \DateTime
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
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
            $errors['name'] = 'Please enter a valid name';
        }
        if (!$this->code) {
            $errors['code'] = 'Please enter a valid code';
        } else {
            // Look for existing subjects with same code
            $c = \App\Db\SubjectMap::create()->findByCode($this->code, $this->institutionId);
            if ($c && $c->id != $this->id) {
                $errors['code'] = 'Subject code already exists';
            }
        }
        
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }
        
        return $errors;
    }
}