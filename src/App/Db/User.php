<?php
namespace App\Db;

use Tk\Auth\Exception;
use App\Auth\Acl;
use Tk\Db\Data;


/**
 * Class User
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class User extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $institutionId = null;

    /**
     * @var string
     */
    public $uid = '';

    /**
     * @var string
     */
    public $username = '';

    /**
     * @var string
     */
    public $password = '';

    /**
     * @var string
     */
    public $role = '';

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $displayName = '';

    /**
     * @var string
     */
    public $email = '';

    /**
     * @var bool
     */
    public $active = true;

    /**
     * @var string
     */
    public $hash = '';

    /**
     * @var string
     */
    public $notes = '';

    /**
     * @var \DateTime
     */
    public $lastLogin = null;

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;

    /**
     * @var \App\Auth\Acl
     */
    private $acl = null;

    /**
     * @var \App\Db\Institution
     */
    private $institution = null;

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
        if (!$this->displayName) {
            $this->displayName = $this->name;
        }
        $this->getHash();
        $this->getData()->save();
        parent::save();
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
     * @return Acl
     */
    public function getAcl()
    {
        if (!$this->acl) {
            $this->acl = new Acl($this);
        }
        return $this->acl;
    }

    /**
     * @param string|array $role
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->getAcl()->hasRole($role);
    }

    /**
     * Create a random password
     *
     * @param int $length
     * @return string
     */
    public static function createPassword($length = 8)
    {
        $chars = '234567890abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
        $i = 0;
        $password = '';
        while ($i <= $length) {
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
            $i++;
        }
        return $password;
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
     * @throws \Tk\Exception
     */
    public function generateHash() 
    {
        if (!$this->username || !$this->role || !$this->email) {
            throw new \Tk\Exception('The username, role and email must be set before generating a valid hash');
        }
        return \App\Factory::hash(sprintf('%s%s%s%s', $this->getVolatileId(), $this->institutionId, $this->username, $this->email));
    }

    /**
     * Set the password from a plain string
     *
     * @param string $pwd
     * @throws Exception
     */
    public function setNewPassword($pwd = '')
    {
        if (!$pwd) {
            $pwd = self::createPassword(10);
        }
        $this->password = \App\Factory::hashPassword($pwd, $this);
    }

    /**
     * Get the institution related to this user
     */
    public function getInstitution()
    {
        if (!$this->institution) {
            $this->institution = \App\Db\InstitutionMap::create()->find($this->institutionId);
            if (!$this->institution && $this->hasRole(\App\Auth\Acl::ROLE_CLIENT)) {
                $this->institution = \App\Db\InstitutionMap::create()->findByOwnerId($this->id);
            }
        }
        return $this->institution;
    }

    /**
     * Get a valid display name
     */
    public function getDisplayName()
    {
        if (!$this->displayName) {
            return $this->name;
        }
        return $this->displayName;
    }

    /**
     * Return the users home|dashboard relative url
     *
     * @note \App\Uri::createHomeUrl() uses this method to get the home path
     *
     * @return string
     * @throws \Exception
     */
    public function getHomeUrl()
    {
        if ($this->getAcl()->isAdmin())
            return '/admin/index.html';
        if ($this->getAcl()->isClient())
            return '/client/index.html';
        if ($this->getAcl()->isStaff())
            return '/staff/index.html';
        if ($this->getAcl()->isStudent())
            return '/student/index.html';
        return '/index.html';   // Should not get here unless their is no roles
    }


    /**
     * Validate this object's current state and return an array
     * with error messages. This will be useful for validating
     * objects for use within forms.
     *
     * @return array
     */
    public function validate()
    {
        $errors = array();

        if (!$this->name) {
            $errors['name'] = 'Invalid field name value';
        }
        if (!$this->role) {
            $errors['role'] = 'Invalid field role value';
        }
        if (!$this->username) {
            $errors['username'] = 'Invalid field username value';
        } else {
            $dup = UserMap::create()->findByUsername($this->username, $this->institutionId);
            if ($dup && $dup->getId() != $this->getId()) {
                $errors['username'] = 'This username is already in use';
            }
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        } else {
            $dup = UserMap::create()->findByEmail($this->email, $this->institutionId);
            if ($dup && $dup->getId() != $this->getId()) {
                $errors['email'] = 'This email is already in use';
            }
        }
        return $errors;
    }

}
