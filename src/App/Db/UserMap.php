<?php
namespace App\Db;

use Tk\Db\Map\Mapper;
use Tk\Db\Map\Model;
use Tk\Db\Tool;
use Tk\Db\Map\ArrayObject;

/**
 * Class UserMapper
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class UserMap extends Mapper
{

    /**
     * Map the form fields data to the object
     *
     * @param array $row
     * @param User $obj
     * @return User
     */
    static function mapForm($row, $obj = null)
    {
        if (!$obj) {
            $obj = new User();
        }
        //$obj->id = $row['id'];
        if (isset($row['username']))
            $obj->username = $row['username'];
        if (isset($row['password']))
            $obj->password = $row['password'];
        if (isset($row['name']))
            $obj->name = $row['name'];
        if (isset($row['role']))
            $obj->role = $row['role'];
        if (isset($row['email']))
            $obj->email = $row['email'];
        if (isset($row['active']))
            $obj->active = ($row['active'] == 'active');

        if (isset($row['modified']))
            $obj->modified = \Tk\Date::createFormDate($row['modified']);
        if (isset($row['created']))
            $obj->created = \Tk\Date::createFormDate($row['created']);

        return $obj;
    }

    /**
     * Unmap the object to an array for the form fields
     *
     * @param $obj
     * @return array
     */
    static function unmapForm($obj)
    {
        $arr = array(
            'id' => $obj->id,
            'username' => $obj->username,
            'password' => $obj->password,
            'name' => $obj->name,
            'role' => $obj->role,
            'email' => $obj->email,
            'active' => (int)$obj->active,
            'modified' => $obj->modified->format(\Tk\Date::$formFormat),
            'created' => $obj->created->format(\Tk\Date::$formFormat)
        );
        return $arr;
    }

    public function map($row)
    {
        $obj = new User();
        $obj->id = $row['id'];
        $obj->username = $row['username'];
        $obj->password = $row['password'];
        $obj->name = $row['name'];
        $obj->role = $row['role'];
        $obj->email = $row['email'];
        $obj->active = ($row['active'] == 1);
        $obj->hash = $row['hash'];
        if ($row['last_login'])
            $obj->lastLogin = \Tk\Date::create($row['last_login']);
        if ($row['modified'])
            $obj->modified = \Tk\Date::create($row['modified']);
        if ($row['created'])
            $obj->created = \Tk\Date::create($row['created']);
        return $obj;
    }

    public function unmap($obj)
    {
        $arr = array(
            'id' => $obj->id,
            'username' => $obj->username,
            'password' => $obj->password,
            'name' => $obj->name,
            'role' => $obj->role,
            'email' => $obj->email,
            'hash' => $obj->hash,
            'active' => (int)$obj->active,
            'modified' => $obj->modified->format(\Tk\Date::ISO_DATE),
            'created' => $obj->created->format(\Tk\Date::ISO_DATE)
        );
        if ($obj->lastLogin) {
            $arr['last_login'] = $obj->lastLogin->format(\Tk\Date::ISO_DATE);
        }
        
        return $arr;
    }


    /**
     *
     * @param $username
     * @param string|array $role
     * @param int $institutionId
     * @return Model
     */
    public function findByUsername($username, $role = null, $institutionId = 0)
    {
        $from = sprintf('%s a', $this->getDb()->quoteParameter($this->getTable()));
        $where = sprintf('a.username = %s', $this->getDb()->quote($username));

        if ($role) {
            if (!is_array($role)) $role = array($role);
            $w = '';
            foreach ($role as $r) {
                $w .= sprintf('a.role = %s OR ', $this->getDb()->quote($r));
            }
            if ($w)
                $where .= ' AND (' . rtrim($w, ' OR ') . ')';
        }

        if ($institutionId > 0) {
            $from .= sprintf(', %s b ', $this->getDb()->quoteParameter('user_institution'));
            $where .= sprintf(' AND b.institution_id = %d', (int)$institutionId);
        }

        $res = $this->selectFrom($from, $where)->current();
        return $res;
    }

    /**
     *
     * @param string $email
     * @param string|array $role
     * @param int $institutionId
     * @return Model
     */
    public function findByEmail($email, $role = null, $institutionId = 0)
    {
        $from = sprintf('%s a', $this->getDb()->quoteParameter($this->getTable()));
        $where = sprintf('a.email = %s', $this->getDb()->quote($email));

        if ($role) {
            if (!is_array($role)) $role = array($role);
            $w = '';
            foreach ($role as $r) {
                $w .= sprintf('a.role = %s OR ', $this->getDb()->quote($r));
            }
            if ($w)
                $where .= ' AND (' . rtrim($w, ' OR ') . ')';
        }

        if ($institutionId > 0) {
            $from .= sprintf(', %s b ', $this->getDb()->quoteParameter('user_institution'));
            $where .= sprintf(' AND b.institution_id = %d', (int)$institutionId);
        }

        $res = $this->selectFrom($from, $where)->current();
        return $res;
    }

    /**
     *
     * @param $role
     * @param null $tool
     * @return Model
     */
    public function findByRole($role, $tool = null)
    {
        if (!is_array($role)) $role = array($role);

        $from = sprintf('%s a', $this->getDb()->quoteParameter($this->getTable()));
        $where = '';
        foreach ($role as $r) {
            $where .= sprintf('a.role = %s OR ', $this->getDb()->quote($r));
        }
        $where = rtrim($where, ' OR ');

        $res = $this->selectFrom($from, $where, $tool);
        return $res;
    }

    /**
     *
     * @param $courseId
     * @param string|array $role
     * @param \Tk\Db\Tool|null $tool
     * @return ArrayObject
     */
    public function findByCourseId($courseId, $role = null, $tool = null)
    {
        $from = sprintf('%s a, user_course b', $this->getDb()->quoteParameter($this->getTable()));
        $where = sprintf('a.id = b.user_id AND b.course_id = %d', (int)$courseId);

        if ($role) {
            if (!is_array($role)) $role = array($role);
            $w = '';
            foreach ($role as $r) {
                $w .= sprintf('a.role = %s OR ', $this->getDb()->quote($r));
            }
            if ($w)
                $where .= ' AND (' . rtrim($w, ' OR ') . ')';
        }
        return $this->selectFrom($from, $where, $tool);
    }

    /**
     *
     * @param int $institutionId
     * @param string|array $role
     * @param \Tk\Db\Tool|null $tool
     * @return ArrayObject
     */
    public function findByInstitutionId($institutionId, $role = null, $tool = null)
    {
        $from = sprintf('%s a, user_institution b', $this->getDb()->quoteParameter($this->getTable()));
        $where = sprintf('a.id = b.user_id AND b.institution_id = %d', (int)$institutionId);
        if ($role) {
            if (!is_array($role)) $role = array($role);
            $w = '';
            foreach ($role as $r) {
                $w .= sprintf('a.role = %s OR ', $this->getDb()->quote($r));
            }
            if ($w)
                $where .= ' AND (' . rtrim($w, ' OR ') . ')';
        }
        return $this->selectFrom($from, $where, $tool);
    }

    /**
     * Find filtered records
     *
     * @param array $filter
     * @param Tool $tool
     * @return ArrayObject
     */
    public function findFiltered($filter = array(), $tool = null)
    {
        $from = sprintf('%s a ', $this->getDb()->quoteParameter($this->getTable()));
        $where = '';

        if (!empty($filter['keywords'])) {
            $kw = '%' . $this->getDb()->escapeString($filter['keywords']) . '%';
            $w = '';
            $w .= sprintf('a.name LIKE %s OR ', $this->getDb()->quote($kw));
            $w .= sprintf('a.username LIKE %s OR ', $this->getDb()->quote($kw));
            $w .= sprintf('a.email LIKE %s OR ', $this->getDb()->quote($kw));
            if (is_numeric($filter['keywords'])) {
                $id = (int)$filter['keywords'];
                $w .= sprintf('a.id = %d OR ', $id);
            }
            if ($w) {
                $where .= '(' . substr($w, 0, -3) . ') AND ';
            }
        }


        if (!empty($filter['institutionId'])) {
            $from .= sprintf(', user_institution b');
            $where .= sprintf('a.id = b.user_id AND b.institution_id = %d AND ', (int)$filter['institutionId']);
        }

        if (!empty($filter['courseId'])) {
            $from .= sprintf(', user_course c');
            $where .= sprintf('a.id = c.user_id AND c.course_id = %d AND ', (int)$filter['courseId']);
        }

        if (!empty($filter['role'])) {
            if (!is_array($filter['role'])) $filter['role'] = array($filter['role']);
            $w = '';
            foreach ($filter['role'] as $r) {
                $w .= sprintf('a.role = %s OR ', $this->getDb()->quote($r));
            }
            if ($w) {
                $where .= '('. rtrim($w, ' OR ') . ') AND ';
            }
        }

        if ($where) {
            $where = substr($where, 0, -4);
        }

        $res = $this->selectFrom($from, $where, $tool);
        return $res;
    }





}