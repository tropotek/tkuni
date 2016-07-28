<?php
namespace App\Db;

use Tk\Db\Map\Mapper;
use Tk\Db\Map\Model;
use Tk\Db\Tool;
use Tk\Db\Map\ArrayObject;

/**
 * Class CourseMap
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class InstitutionMap extends Mapper
{

    /**
     * Map the form fields data to the object
     *
     * @param array $row
     * @param Institution $obj
     * @return Institution
     */
    static function mapForm($row, $obj = null)
    {
        if (!$obj) {
            $obj = new Institution();
        }
        //$obj->id = $row['id'];
        if (isset($row['ownerId']))
            $obj->ownerId = $row['ownerId'];
        if (isset($row['name']))
            $obj->name = $row['name'];
        if (isset($row['domain']))
            $obj->domain = $row['domain'];
        if (isset($row['email']))
            $obj->email = $row['email'];
        if (isset($row['description']))
            $obj->description = $row['description'];
        if (isset($row['logo']))
            $obj->logo = $row['logo'];
        if (isset($row['active']))
            $obj->active = ($row['active'] == 'active');
        if (isset($row['hash']))
            $obj->hash = $row['hash'];

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
            'ownerId' => $obj->ownerId,
            'name' => $obj->name,
            'domain' => $obj->domain,
            'email' => $obj->email,
            'description' => $obj->description,
            'logo' => $obj->logo,
            'active' => $obj->active ? 'active' : '',
            'hash' => $obj->hash,
            'modified' => $obj->modified->format(\Tk\Date::$formFormat),
            'created' => $obj->created->format(\Tk\Date::$formFormat)
        );

        return $arr;
    }

    public function map($row)
    {
        $obj = new Institution();
        $obj->id = $row['id'];
        $obj->ownerId = $row['owner_id'];
        $obj->name = $row['name'];
        $obj->domain = $row['domain'];
        $obj->email = $row['email'];
        $obj->description = $row['description'];
        $obj->logo = $row['logo'];
        $obj->active = ($row['active'] == 1);
        $obj->hash = $row['hash'];
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
            'owner_id' => $obj->ownerId,
            'name' => $obj->name,
            'domain' => $obj->domain,
            'email' => $obj->email,
            'description' => $obj->description,
            'logo' => $obj->logo,
            'active' => (int)$obj->active,
            'hash' => $obj->hash,
            'modified' => $obj->modified->format(\Tk\Date::ISO_DATE),
            'created' => $obj->created->format(\Tk\Date::ISO_DATE)
        );
        return $arr;
    }

    /**
     *
     * @param null|\Tk\Db\Tool $tool
     * @return ArrayObject
     */
    public function findActive($tool = null)
    {
        $where = sprintf('active = 1');
        return $this->select($where, $tool);
    }

    /**
     *
     * @param $hash
     * @param int $active
     * @return Institution|null
     */
    public function findByhash($hash, $active = 1)
    {
        $where = sprintf('hash = %s AND active = %s', $this->getDb()->quote($hash), (int)$active);
        return $this->select($where)->current();
    }

    /**
     *
     * @param $domain
     * @return Institution|null
     */
    public function findByDomain($domain)
    {
        $where = sprintf('domain = %s', $this->getDb()->quote($domain));
        return $this->select($where)->current();
    }
    
    /**
     * 
     * @param int $courseId
     * @param Tool $tool
     * @return ArrayObject
     */
    public function findByUserId($courseId, $tool = null)
    {
        $from = sprintf('%s a, user_course b', $this->getDb()->quoteParameter($this->getTable()));
        $where = sprintf('a.id = b.course_id AND b.user_id = %d', (int)$courseId);
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
            $w .= sprintf('a.code LIKE %s OR ', $this->getDb()->quote($kw));
            $w .= sprintf('a.email LIKE %s OR ', $this->getDb()->quote($kw));
            $w .= sprintf('a.description LIKE %s OR ', $this->getDb()->quote($kw));
            if (is_numeric($filter['keywords'])) {
                $id = (int)$filter['keywords'];
                $w .= sprintf('a.id = %d OR ', $id);
            }
            if ($w) {
                $where .= '(' . substr($w, 0, -3) . ') AND ';
            }
        }

        if (!empty($filter['email'])) {
            $where .= sprintf('a.email = %s AND ', $this->getDb()->quote($filter['email']));
        }

        if (!empty($filter['active'])) {
            $where .= sprintf('a.active = %s AND ', (int)$filter['active']);
        }

        if ($where) {
            $where = substr($where, 0, -4);
        }

        $res = $this->selectFrom($from, $where, $tool);
        return $res;
    }


    /**
     * @param $courseId
     * @param $userId
     * @return boolean
     */
    public function hasUser($courseId, $userId)
    {
        $sql = sprintf('SELECT * FROM user_institution WHERE course_id = %d AND user_id = %d', (int)$courseId, (int)$userId);
        return ($this->getDb()->query($sql)->rowCount() > 0);
    }

    /**
     * @param $courseId
     * @param $userId
     * @return \Tk\Db\PDOStatement
     */
    public function deleteUser($courseId, $userId)
    {
        $query = sprintf('DELETE FROM user_institution WHERE user_id = %d AND course_id = %d', (int)$userId, (int)$courseId);
        return $this->getDb()->exec($query);
    }

    /**
     * @param $courseId
     * @param $userId
     * @return \Tk\Db\PDOStatement
     */
    public function addUser($courseId, $userId)
    {
        $query = sprintf('INSERT INTO user_institution (user_id, course_id)  VALUES (%d, %d) ', (int)$userId, (int)$courseId);
        return $this->getDb()->exec($query);
    }

    /**
     * @param int $courseId
     * @return \Tk\Db\PDOStatement
     */
    public function deleteAllUsers($courseId)
    {
        $query = sprintf('DELETE FROM user_institution WHERE course_id = %d ', (int)$courseId);
        return $this->getDb()->exec($query);
    }

}