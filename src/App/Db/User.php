<?php
namespace App\Db;

use Tk\Db\Data;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class User extends \Tk\Db\Map\Model implements \Tk\ValidInterface, \Uni\Db\UserIface
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
     * @var string
     */
    public $sessionId = '';

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
     * @throws \Tk\Exception
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
     * @throws \Tk\Db\Exception
     */
    public function getData()
    {
        if (!$this->data)
            $this->data = \Tk\Db\Data::create(get_class($this), $this->id);
        return $this->data;
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
     * @throws \Tk\Exception
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
        return \App\Config::getInstance()->hash(sprintf('%s%s%s%s', $this->getVolatileId(), $this->institutionId, $this->username, $this->email));
    }

    /**
     * Set the password from a plain string
     *
     * @param string $pwd
     * @throws \Tk\Exception
     */
    public function setNewPassword($pwd = '')
    {
        if (!$pwd) {
            $pwd = self::createPassword(10);
        }
        $this->password = \App\Config::getInstance()->hashPassword($pwd, $this);
    }

    /**
     * Get the institution related to this user
     * @throws \Tk\Db\Exception
     */
    public function getInstitution()
    {
        if (!$this->institution) {
            $this->institution = \App\Db\InstitutionMap::create()->find($this->institutionId);
            if (!$this->institution && $this->hasRole(\App\Db\User::ROLE_CLIENT)) {
                $this->institution = \App\Db\InstitutionMap::create()->findByOwnerId($this->id);
            }
        }
        return $this->institution;
    }

    /**
     * Get a valid display name
     */
    public function getName()
    {
        if (!$this->displayName) {
            return $this->name;
        }
        return $this->displayName;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Return the users home|dashboard relative url
     *
     * @note \Uni\Uri::createHomeUrl() uses this method to get the home path
     *
     * @return \Tk\Uri
     */
    public function getHomeUrl()
    {
        if ($this->isAdmin())
            return \Tk\Uri::create('/admin/index.html');
        if ($this->isClient())
            return \Tk\Uri::create('/client/index.html');
        if ($this->isStaff())
            return \Tk\Uri::create('/staff/index.html');
        if ($this->isStudent())
            return \Tk\Uri::create('/student/index.html');
        return \Tk\Uri::create('/index.html');   // Should not get here unless their is no roles
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param string|array $role
     * @return boolean
     */
    public function hasRole($role)
    {
        if (!is_array($role)) $role = array($role);
        foreach ($role as $r) {
            if ($r == $this->role || preg_match('/'.preg_quote($r).'/', $this->role)) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    /**
     *
     * @return boolean
     */
    public function isClient()
    {
        return $this->hasRole(self::ROLE_CLIENT);
    }

    /**
     *
     * @return boolean
     */
    public function isStaff()
    {
        return $this->hasRole(self::ROLE_STAFF);
    }

    /**
     *
     * @return boolean
     */
    public function isStudent()
    {
        return $this->hasRole(self::ROLE_STUDENT);
    }

    /**
     * Returns true if the user is enrolled fully into the subject
     *
     * @param $subjectId
     * @return bool
     * @throws \Tk\Db\Exception
     */
    public function isEnrolled($subjectId)
    {
        return \App\Db\SubjectMap::create()->hasUser($subjectId, $this->getVolatileId());
    }

    /**
     * Validate this object's current state and return an array
     * with error messages. This will be useful for validating
     * objects for use within forms.
     *
     * @return array
     * @throws \ReflectionException
     * @throws \Tk\Db\Exception
     * @throws \Tk\Db\Exception
     */
    public function validate()
    {
        $errors = array();

        if (!$this->name) {
            $errors['name'] = 'Invalid field name value';
        }
        
        if (!$this->role || !in_array($this->role, \Tk\Object::getClassConstants($this, 'ROLE_'))) {
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
