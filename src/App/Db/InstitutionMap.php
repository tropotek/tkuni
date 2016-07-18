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
     * @param Role $obj
     * @return Role
     */
    static function mapForm($row, $obj = null)
    {
        if (!$obj) {
            $obj = new Course();
        }
        //$obj->id = $row['id'];
        if (isset($row['name']))
            $obj->name = $row['name'];
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
            $obj->modified = \Tk\Date::create($row['modified']);
        if (isset($row['created']))
            $obj->created = \Tk\Date::create($row['created']);
        
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
        $start = null;
        $finish = null;
        $dateFormat = 'd/m/Y';

        $arr = array(
            'id' => $obj->id,
            'name' => $obj->name,
            'email' => $obj->email,
            'description' => $obj->description,
            'logo' => $obj->logo,
            'active' => (int)$obj->active,
            'hash' => $obj->hash,
            'modified' => $obj->modified->format($dateFormat),
            'created' => $obj->created->format($dateFormat)
        );

        return $arr;
    }

    public function map($row)
    {
        $obj = new Course();
        $obj->id = $row['id'];
        $obj->name = $row['name'];
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
            'name' => $obj->name,
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
     * @param string $code
     * @return Model
     */
    public function findByCode($code)
    {
        return $this->select('code = ' . $this->getDb()->quote($code))->current();
    }
    
    /**
     * 
     * @param int $courseId
     * @param Tool $tool
     * @return ArrayObject
     */
    public function findByUserId($courseId, $tool = null)
    {
        $from = sprintf('%s a, user_course_role b', $this->getDb()->quoteParameter($this->getTable()));
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

    
    
}