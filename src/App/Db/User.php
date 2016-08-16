<?php
namespace App\Db;

use Tk\Auth;
use Tk\Auth\Exception;
use App\Auth\Acl;


/**
 * Class User
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class User extends \Tk\Db\Map\Model
{
    static $HASH_FUNCTION = 'md5';


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
    private $access = null;

    /**
     * @var \App\Db\Institution
     */
    private $institution = null;


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
        parent::save();
    }

    /**
     * @return Acl
     */
    public function getAcl()
    {
        if (!$this->access) {
            $this->access = new Acl($this);
        }
        return $this->access;
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
        // TODO: We should really add the institutionId to this hash
        return hash(self::$HASH_FUNCTION, sprintf('%s%s%s', $this->username, $this->role, $this->email));
    }

    /**
     * Set the password from a plain string
     *
     * @param string $pwd
     * @throws Exception
     */
    public function setPassword($pwd = '')
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
        }
        return $this->institution;
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


}


class UserValidator extends \Tk\Db\Map\Validator
{

    /**
     * Implement the validating rules to apply.
     *
     */
    protected function validate()
    {
        /** @var User $obj */
        $obj = $this->getObject();

        if (!$obj->name) {
            $this->addError('name', 'Invalid field name value.');
        }
        if (!$obj->role) {
            $this->addError('role', 'Invalid field role value.');
        }
        if (!$obj->username) {
            $this->addError('username', 'Invalid field username value.');
        } else {
            //$dup = UserMap::create()->findByUsername($obj->username, $obj->role);
            $dup = UserMap::create()->findByUsername($obj->username, $obj->institutionId);
            if ($dup && $dup->getId() != $obj->getId()) {
                $this->addError('username', 'This username is already in use.');
            }
        }

        if (!filter_var($obj->email, FILTER_VALIDATE_EMAIL)) {
            $this->addError('email', 'Please enter a valid email address');
        } else {
            //$dup = UserMap::create()->findByEmail($obj->email, $obj->role);
            $dup = UserMap::create()->findByEmail($obj->email, $obj->institutionId);
            if ($dup && $dup->getId() != $obj->getId()) {
                $this->addError('email', 'This email is already in use.');
            }
        }

    }
}
