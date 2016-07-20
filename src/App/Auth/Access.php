<?php
namespace App\Auth;

use \App\Db\Role;

/**
 * Class RoleAccess
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Access 
{
    const ROLE_ADMIN = 'admin';
    const ROLE_CLIENT= 'client';
    const ROLE_STAFF = 'staff';
    const ROLE_STUDENT = 'student';

    
    /**
     * @var \App\Db\User
     */
    protected $user = null;

    /**
     * Access constructor.
     *
     * @param \App\Db\User $user
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * A static constructor so we can call this method inline:
     * Eg:
     *   - Access::create($user)->isAdmin();
     * 
     * @param \App\Db\User $user
     * @return Access
     */
    static function create($user)
    {
        $obj = new static($user);
        return $obj;
    }

    /**
     * @param string|array $role
     * @return boolean
     */
    public function hasRole($role) 
    {
        if (!is_array($role)) $role = array($role);
        foreach ($role as $r) {
            if ($r == $this->user->role || preg_match('/'.preg_quote($r).'/', $this->user->role)) {
                return true;
            }
        }
        return false;

//        if (!is_array($role)) $role = array($role);
//
//        foreach ($role as $r) {
//            if (!$r instanceof Role) {
//                $r = Role::getMapper()->findByName($r);
//            }
//            if ($r) {
//                $obj = Role::getMapper()->findRole($r->id, $this->user->id);
//                if ($obj && $obj->id = $r->id) {
//                    return true;
//                }
//            }
//        }
//        return false;
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
    
    
    
}