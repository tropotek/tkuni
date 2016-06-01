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
class CourseMap extends Mapper
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
        if (isset($row['lti_consumer_key']))
            $obj->lti_consumer_key = $row['lti_consumer_key'];
        if (isset($row['lti_context_id']))
            $obj->lti_context_id = $row['lti_context_id'];
        if (isset($row['name']))
            $obj->name = $row['name'];
        if (isset($row['code']))
            $obj->code = $row['code'];
        if (isset($row['email']))
            $obj->email = $row['email'];
        if (isset($row['description']))
            $obj->description = $row['description'];
        
        // TODO: This has to be tested, should parse date string using config['system.date.format.php']
        if (isset($row['start']))
            $obj->start = new \DateTime($row['start']);
        if (isset($row['finish']))
            $obj->finish = new \DateTime($row['finish']);
        
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
        $start = null;
        $finish = null;
        $dateFormat = 'd/m/Y';
        
        
        // TODO: This has to be tested, should parse date string using config['system.date.format.php']
        if ($obj->start) {
            $start = $obj->start->format($dateFormat);
        }
        if ($obj->finish) {
            $finish = $obj->finish->format($dateFormat);
        }
        
        $arr = array(
            'id' => $obj->id,
            'lti_consumer_key' => $obj->lti_consumer_key,
            'lti_context_id' => $obj->lti_context_id,
            'name' => $obj->name,
            'code' => $obj->code,
            'email' => $obj->email,
            'description' => $obj->description,
            'start' => $start,
            'finish' => $finish,
            'active' => (int)$obj->active,
            'modified' => $obj->modified->format($dateFormat),
            'created' => $obj->created->format($dateFormat)
        );
        
        
        
        return $arr;
    }

    public function map($row)
    {
        $obj = new Course();
        $obj->id = $row['id'];
        $obj->lti_consumer_key = $row['lti_consumer_key'];
        $obj->lti_context_id = $row['lti_context_id'];
        $obj->name = $row['name'];
        $obj->code = $row['code'];
        $obj->email = $row['email'];
        $obj->description = $row['description'];
        if ($row['start'])
            $obj->start = \Tk\Date::create($row['start']);
        if ($row['finish'])
            $obj->finish = \Tk\Date::create($row['finish']);
        $obj->active = ($row['active'] == 1);
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
            'lti_consumer_key' => $obj->lti_consumer_key,
            'lti_context_id' => $obj->lti_context_id,
            'name' => $obj->name,
            'code' => $obj->code,
            'email' => $obj->email,
            'description' => $obj->description,
            'start' => $obj->start->format(\Tk\Date::ISO_DATE),
            'finish' => $obj->finish->format(\Tk\Date::ISO_DATE),
            'active' => (int)$obj->active,
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

        if (!empty($filter['lti_consumer_key'])) {
            $where .= sprintf('a.lti_consumer_key = %s AND ', $this->getDb()->quote($filter['lti_consumer_key']));
        }

        if (!empty($filter['lti_context_id'])) {
            $where .= sprintf('a.lti_context_id = %s AND ', $this->getDb()->quote($filter['lti_context_id']));
        }

        if (!empty($filter['code'])) {
            $where .= sprintf('a.code = %s AND ', $this->getDb()->quote($filter['code']));
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
     * @param $userId
     * @return \Tk\Db\PDOStatement
     */
    public function deleteAllUserCourses($userId)
    {
        $query = sprintf('DELETE FROM user_course WHERE user_id = %d ', (int)$userId);
        return $this->getDb()->exec($query);
    }

    /**
     * @param $courseId
     * @param $userId
     * @return \Tk\Db\PDOStatement
     */
    public function deleteUserCourse($courseId, $userId)
    {
        $query = sprintf('DELETE FROM user_course WHERE user_id = %d AND course_id = %d', (int)$userId, (int)$courseId);
        return $this->getDb()->exec($query);
    }

    /**
     * @param $courseId
     * @param $userId
     * @return \Tk\Db\PDOStatement
     */
    public function addUserCourse($courseId, $userId)
    {
        $query = sprintf('INSERT INTO user_course (user_id, course_id)  VALUES (%d, %d) ', (int)$userId, (int)$courseId);
        return $this->getDb()->exec($query);
    }

    /**
     * @param $courseId
     * @param $userId
     * @return boolean
     */
    public function inCourse($courseId, $userId)
    {
        $from = sprintf('%s a, user_course b', $this->getDb()->quoteParameter($this->getTable()));
        $where = sprintf('a.id = %d AND a.id = b.course_id AND b.user_id = %d', (int)$courseId, (int)$userId);
        return ($this->selectFrom($from, $where)->current() != null);
    }
    

}