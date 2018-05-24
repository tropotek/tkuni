<?php
namespace App\Db;

use Tk\Db\Data;
use Uni\Db\SubjectIface;

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
    public $userId = 0;

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
    private $user = null;

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
     * @throws \Tk\Db\Exception
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
     * @throws \Tk\Db\Exception
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
     * @throws \Tk\Db\Exception
     */
    public function generateHash()
    {
        return hash('md5', sprintf('%s', $this->getVolatileId()));
    }

    /**
     * Get the path for all file associated to this object
     *
     * @return string
     * @throws \Tk\Db\Exception
     */
    public function getDataPath()
    {
        return sprintf('/institution/%s', $this->getVolatileId());
    }

    /**
     * Get the institution data object
     *
     * @return Data
     * @throws \Tk\Db\Exception
     */
    public function getData()
    {
        if (!$this->data)
            $this->data = Data::create(get_class($this), $this->getVolatileId());
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
     * @throws \Tk\Db\Exception
     */
    public function getUser()
    {
        if (!$this->user)
            $this->user = \App\Db\UserMap::create()->find($this->userId);
        return $this->user;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
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
     * @param string $subjectCode
     * @return SubjectIface
     */
    public function findSubjectByCode($subjectCode)
    {
        return \App\Db\SubjectMap::create()->findByCode($subjectCode, $this->getId());
    }

    /**
     * @param int $subjectId
     * @return \Tk\Db\Map\Model|\Tk\Db\ModelInterface|SubjectIface
     * @throws \Tk\Db\Exception
     */
    public function findSubject($subjectId)
    {
        return \App\Db\SubjectMap::create()->find($subjectId);
    }
}
