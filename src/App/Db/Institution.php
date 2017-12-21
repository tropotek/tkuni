<?php
namespace App\Db;

use Tk\Db\Data;
use Uni\Db\CourseIface;

/**
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Institution extends \Tk\Db\Map\Model implements \Tk\ValidInterface, \Uni\Db\InstitutionIface
{

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $ownerId = 0;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $domain = '';

    /**
     * @var string
     */
    public $email = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var string
     */
    public $logo = '';

    /**
     * @var boolean
     */
    public $active = true;

    /**
     * @var string
     */
    public $hash = '';

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;


    /**
     * @var User
     */
    private $owner = null;

    /**
     * @var Data
     */
    private $data = null;
    
    

    /**
     *
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
        $this->getHash();
        $this->getData()->save();
        parent::save();
    }

    /**
     * Get the user hash or generate one if needed
     *
     * @return string
     */
    public function getHash()
    {
        if (!$this->hash) {
            $this->hash = $this->generateHash();
        }
        return $this->hash;
    }

    /**
     * Helper method to generate user hash
     *
     * @return string
     */
    public function generateHash()
    {
        return hash('md5', sprintf('%s', $this->getVolatileId()));
    }

    /**
     * Get the path for all file associated to this object
     *
     * @return string
     */
    public function getDataPath()
    {
        return sprintf('/institution/%s', $this->getVolatileId());
    }

    /**
     * Get the institution data object
     *
     * @return Data
     */
    public function getData()
    {
        if (!$this->data)
            $this->data = Data::create($this->id, get_class($this));
        return $this->data;
    }

    /**
     * Returns null if no logo available
     *
     * @return \Tk\Uri|null
     */
    public function getLogoUrl()
    {
        if ($this->logo)
            return \Tk\Uri::create(\App\Config::getInstance()->getDataUrl().$this->logo);
    }

    /**
     * Find this institutions owner user
     *
     * @return User
     */
    public function getOwner()
    {
        if (!$this->owner)
            $this->owner = \App\Db\UserMap::create()->find($this->ownerId);
        return $this->owner;
    }

    /**
     * @return int
     */
    public function getOwnerId()
    {
        return $this->ownerId;
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
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }


    /**
     * Implement the validating rules to apply.
     *
     */
    public function validate()
    {
        $error = array();

        if (!$this->name) {
            $error['name'] = 'Invalid field value';
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $error['email'] = 'Please enter a valid email address';
        }

        // Ensure the domain is unique if set.
        if ($this->domain) {
            //if (!preg_match('/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/g', $obj->domain)) {
            if (!preg_match(self::REG_DOMAIN, $this->domain)) {
                $error['domain'] = 'Please enter a valid domain name (EG: example.com.au)';
            } else {
                $dup = InstitutionMap::create()->findByDomain($this->domain);
                if ($dup && $dup->getId() != $this->getId()) {
                    $error['domain'] = 'This domain name is already in use';
                }
            }
        }

        return $error;
    }

    /**
     * @param string $courseCode
     * @return CourseIface
     */
    public function findCourseByCode($courseCode)
    {
        return \App\Db\CourseMap::create()->findByCode($courseCode, $this->getId());
    }

    /**
     * @param int $courseId
     * @return CourseIface
     */
    public function findCourse($courseId)
    {
        return \App\Db\CourseMap::create()->find($courseId);
    }
}
