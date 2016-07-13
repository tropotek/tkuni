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
class RoleMap extends Mapper
{

    /**
     * Map the form fields data to the object
     *
     * @param array $row
     * @param Role $obj
     * @return Role
     */
    static function mapForm($row, $obj = null)
    {
        if (!$obj) {
            $obj = new Role();
        }
        //$obj->id = $row['id'];
        if (isset($row['name']))
            $obj->name = $row['name'];
        if (isset($row['description']))
            $obj->description = $row['description'];

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
            'name' => $obj->name,
            'description' => $obj->description
        );
        return $arr;
    }

    public function map($row)
    {
        $obj = new Role();
        $obj->id = $row['id'];
        $obj->name = $row['name'];
        $obj->description = $row['description'];
        return $obj;
    }

    public function unmap($obj)
    {
        $arr = array(
            'id' => $obj->id,
            'name' => $obj->name,
            'description' => $obj->description
        );
        return $arr;
    }

    /**
     * @param $name
     * @return Model
     */
    public function findByName($name)
    {
        return $this->select('name = ' . $this->getDb()->quote($name))->current();
    }


    /**
     * @param int $userId
     * @param Tool $tool
     * @return ArrayObject
     */
    public function findByUserId($userId, $tool = null)
    {
        $from = sprintf('%s a, user_role b', $this->getDb()->quoteParameter($this->getTable()));
        $where = sprintf('a.id = b.role_id AND b.user_id = %d', (int)$userId);
        return $this->selectFrom($from, $where, $tool);
    }

    /**
     * @param $roleId
     * @param $userId
     * @return Role
     */
    public function findRole($roleId, $userId)
    {
        $from = sprintf('%s a, user_role b', $this->getDb()->quoteParameter($this->getTable()));
        $where = sprintf('a.id = %d AND a.id = b.role_id AND b.user_id = %d', (int)$roleId, (int)$userId);
        return $this->selectFrom($from, $where)->current();
    }

    /**
     * @param $userId
     * @param $courseId
     * @return Role
     */
    public function findCourseRole($userId, $courseId)
    {
        $from = sprintf('%s a, user_course_role b', $this->getDb()->quoteParameter($this->getTable()));
        $where = sprintf('a.id = b.role_id AND b.user_id = %d AND b.course_id = %d ', (int)$userId, (int)$courseId);
        return $this->selectFrom($from, $where)->current();
    }




    /**
     * @param $userId
     * @return \Tk\Db\PDOStatement
     */
    public function deleteAllUserRoles($userId)
    {
        $query = sprintf('DELETE FROM user_role WHERE user_id = %d', (int)$userId);
        return $this->getDb()->exec($query);
    }

    /**
     * @param $roleId
     * @param $userId
     * @return \Tk\Db\PDOStatement
     */
    public function deleteUserRole($roleId, $userId)
    {
        $query = sprintf('DELETE FROM user_role WHERE user_id = %d AND role_id = %d', (int)$userId, (int)$roleId);
        return $this->getDb()->exec($query);
    }

    /**
     * @param $roleId
     * @param $userId
     * @return \Tk\Db\PDOStatement
     */
    public function addUserRole($roleId, $userId)
    {
        $query = sprintf('INSERT INTO user_role (user_id, role_id)  VALUES (%d, %d)', (int)$userId, (int)$roleId);
        return $this->getDb()->exec($query);
    }


}