<?php
namespace App\Db;

use Tk\Db\Map\Model;
use Tk\Db\Tool;
use Tk\Db\Map\ArrayObject;
use Tk\DataMap\Db;
use Tk\DataMap\Form;

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
     *
     * @return \Tk\DataMap\DataMap
     */
    public function getDbMap()
    {
        if (!$this->dbMap) {
            $this->dbMap = new \Tk\DataMap\DataMap();
            $this->dbMap->addProperty(new Db\Number('id'), 'key');
            $this->dbMap->addProperty(new Db\Number('institutionId', 'institution_id'));
            $this->dbMap->addProperty(new Db\Text('uid'));
            $this->dbMap->addProperty(new Db\Text('username'));
            $this->dbMap->addProperty(new Db\Text('password'));
            $this->dbMap->addProperty(new Db\Text('name'));
            $this->dbMap->addProperty(new Db\Text('role'));
            $this->dbMap->addProperty(new Db\Text('email'));
            $this->dbMap->addProperty(new Db\Boolean('active'));
            $this->dbMap->addProperty(new Db\Text('hash'));
            $this->dbMap->addProperty(new Db\Date('lastLogin', 'last_login'));
            $this->dbMap->addProperty(new Db\Date('modified'));
            $this->dbMap->addProperty(new Db\Date('created'));

            $this->setMarkDeleted('del');
            $this->setPrimaryKey($this->dbMap->currentProperty('key')->getColumnName());
        }
        return $this->dbMap;
    }

    /**
     *
     * @return \Tk\DataMap\DataMap
     */
    public function getFormMap()
    {
        if (!$this->formMap) {
            $this->formMap = new \Tk\DataMap\DataMap();
            $this->formMap->addProperty(new Form\Number('id'), 'key');
            $this->formMap->addProperty(new Form\Number('institutionId'));
            $this->formMap->addProperty(new Form\Text('uid'));
            $this->formMap->addProperty(new Form\Text('username'));
            $this->formMap->addProperty(new Form\Text('password'));
            $this->formMap->addProperty(new Form\Text('name'));
            $this->formMap->addProperty(new Form\Text('role'));
            $this->formMap->addProperty(new Form\Text('email'));
            $this->formMap->addProperty(new Form\Boolean('active'));

            $this->setPrimaryKey($this->formMap->currentProperty('key')->getColumnName());
        }
        return $this->formMap;
    }



    /**
     *
     * @param $hash
     * @param int $institutionId
     * @param string|array $role
     * @return Institution|null
     */
    public function findByhash($hash, $institutionId = 0, $role = null)
    {
        $where = sprintf('hash = %s ', $this->getDb()->quote($hash));
        if ($institutionId > 0) {
            $where .= sprintf(' AND a.institution_id = %d', (int)$institutionId);
        } else {
            $where .= sprintf(' AND (a.institution_id IS NULL OR a.institution_id = 0)');
        }
        if ($role) {
            if (!is_array($role)) $role = array($role);
            $w = '';
            foreach ($role as $r) {
                $w .= sprintf('a.role = %s OR ', $this->getDb()->quote($r));
            }
            if ($w)
                $where .= ' AND (' . rtrim($w, ' OR ') . ')';
        }
        return $this->select($where)->current();
    }

    /**
     *
     * @param $username
     * @param int $institutionId
     * @param string|array $role
     * @return Model
     */
    public function findByUsername($username, $institutionId = 0, $role = null)
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
            $where .= sprintf(' AND a.institution_id = %d', (int)$institutionId);
        } else {
            $where .= sprintf(' AND (a.institution_id IS NULL OR a.institution_id = 0)');
        }
        $res = $this->selectFrom($from, $where)->current();

        vd($res);
        return $res;
    }

    /**
     *
     * @param string $email
     * @param int $institutionId
     * @param string|array $role
     * @return Model
     */
    public function findByEmail($email, $institutionId = null, $role = null)
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
            $where .= sprintf(' AND a.institution_id = %d', (int)$institutionId);
        } else {
            $where .= sprintf(' AND (a.institution_id IS NULL OR a.institution_id = 0)');
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
//    public function findByRole($role, $tool = null)
//    {
//        if (!is_array($role)) $role = array($role);
//
//        $from = sprintf('%s a', $this->getDb()->quoteParameter($this->getTable()));
//        $where = '';
//        foreach ($role as $r) {
//            $where .= sprintf('a.role = %s OR ', $this->getDb()->quote($r));
//        }
//        $where = rtrim($where, ' OR ');
//
//        $res = $this->selectFrom($from, $where, $tool);
//        return $res;
//    }

    /**
     *
     * @param $courseId
     * @param string|array $role
     * @param \Tk\Db\Tool|null $tool
     * @return ArrayObject
     */
//    public function findByCourseId($courseId, $role = null, $tool = null)
//    {
//        $from = sprintf('%s a, user_course b', $this->getDb()->quoteParameter($this->getTable()));
//        $where = sprintf('a.id = b.user_id AND b.course_id = %d', (int)$courseId);
//
//        if ($role) {
//            if (!is_array($role)) $role = array($role);
//            $w = '';
//            foreach ($role as $r) {
//                $w .= sprintf('a.role = %s OR ', $this->getDb()->quote($r));
//            }
//            if ($w)
//                $where .= ' AND (' . rtrim($w, ' OR ') . ')';
//        }
//        return $this->selectFrom($from, $where, $tool);
//    }

    /**
     *
     * @param int $institutionId
     * @param string|array $role
     * @param \Tk\Db\Tool|null $tool
     * @return ArrayObject
     */
    public function findByInstitutionId($institutionId, $role = null, $tool = null)
    {

        $where = sprintf('institution_id = %s ', (int)$institutionId);
        if ($role) {
            if (!is_array($role)) $role = array($role);
            $w = '';
            foreach ($role as $r) {
                $w .= sprintf('a.role = %s OR ', $this->getDb()->quote($r));
            }
            if ($w)
                $where .= ' AND (' . rtrim($w, ' OR ') . ')';
        }
        return $this->select($where, $tool);
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

        if (!empty($filter['uid'])) {
            $where .= sprintf('a.uid = %s AND ', $this->getDb()->quote($filter['uid']));
        }

        if (!empty($filter['institutionId'])) {
            //$from .= sprintf(', user_institution b');
            $where .= sprintf('a.institution_id = %d AND ', (int)$filter['institutionId']);
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