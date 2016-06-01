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
        if (isset($row['uid']))
            $obj->uid = $row['uid'];
        if (isset($row['username']))
            $obj->username = $row['username'];
        if (isset($row['password']))
            $obj->password = $row['password'];
        if (isset($row['name']))
            $obj->name = $row['name'];
        if (isset($row['email']))
            $obj->email = $row['email'];
        if (isset($row['active']))
            $obj->active = ($row['active'] == 'active');

        // TODO: This has to be tested, should parse date string using config['system.date.format.php']
        if (isset($row['modified']))
            $obj->modified = new \DateTime($row['modified']);
        if (isset($row['created']))
            $obj->created = new \DateTime($row['created']);

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
        // Get the roles
        $roles = Role::getMapper()->findByUserId($obj->id);
        $l = array();
        foreach ($roles as $i => $o) {
            $l[$i] = $o->id;
        }

        $arr = array(
            'id' => $obj->id,
            'uid' => $obj->uid,
            'username' => $obj->username,
            'password' => $obj->password,
            'name' => $obj->name,
            'email' => $obj->email,
            'role' => $l,
            'active' => (int)$obj->active,
            'modified' => $obj->modified->format(\Tk\Date::ISO_DATE),
            'created' => $obj->created->format(\Tk\Date::ISO_DATE)
        );
        return $arr;
    }

    public function map($row)
    {
        $obj = new User();
        $obj->id = $row['id'];
        $obj->uid = $row['uid'];
        $obj->username = $row['username'];
        $obj->password = $row['password'];
        $obj->name = $row['name'];
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
            'uid' => $obj->uid,
            'username' => $obj->username,
            'password' => $obj->password,
            'name' => $obj->name,
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

    public function findByUsername($username)
    {
        return $this->select('username = ' . $this->getDb()->quote($username))->current();
    }

    public function findByUid($uid)
    {
        return $this->select('uid = ' . $this->getDb()->quote($uid))->current();
    }

    public function findByEmail($email)
    {
        return $this->select('email = ' . $this->getDb()->quote($email))->current();
    }

    public function findByCourseId($courseId, $tool = null)
    {
        $from = sprintf('%s a, user_course b', $this->getDb()->quoteParameter($this->getTable()));
        $where = sprintf('a.id = b.user_id AND b.course_id = %d', (int)$courseId);
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
        
//        if (!empty($filter['lti_context_id'])) {
//            $where .= sprintf('a.lti_context_id = %s AND ', $this->getDb()->quote($filter['lti_context_id']));
//        }


        if ($where) {
            $where = substr($where, 0, -4);
        }

        $res = $this->selectFrom($from, $where, $tool);
        return $res;
    }

}