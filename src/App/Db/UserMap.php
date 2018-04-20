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
            $this->dbMap->addPropertyMap(new Db\Integer('id'), 'key');
            $this->dbMap->addPropertyMap(new Db\Integer('institutionId', 'institution_id'));
            $this->dbMap->addPropertyMap(new Db\Text('uid'));
            $this->dbMap->addPropertyMap(new Db\Text('username'));
            $this->dbMap->addPropertyMap(new Db\Text('password'));
            $this->dbMap->addPropertyMap(new Db\Text('name'));
            $this->dbMap->addPropertyMap(new Db\Text('role'));
            $this->dbMap->addPropertyMap(new Db\Text('email'));
            $this->dbMap->addPropertyMap(new Db\Date('lastLogin', 'last_login'));
            $this->dbMap->addPropertyMap(new Db\Text('notes'));
            $this->dbMap->addPropertyMap(new Db\Text('sessionId', 'session_id'));
            $this->dbMap->addPropertyMap(new Db\Boolean('active'));
            $this->dbMap->addPropertyMap(new Db\Text('hash'));
            $this->dbMap->addPropertyMap(new Db\Date('modified'));
            $this->dbMap->addPropertyMap(new Db\Date('created'));
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
            $this->formMap->addPropertyMap(new Form\Integer('id'), 'key');
            $this->formMap->addPropertyMap(new Form\Integer('institutionId'));
            $this->formMap->addPropertyMap(new Form\Text('uid'));
            $this->formMap->addPropertyMap(new Form\Text('username'));
            $this->formMap->addPropertyMap(new Form\Text('password'));
            $this->formMap->addPropertyMap(new Form\Text('name'));
            $this->formMap->addPropertyMap(new Form\Text('role'));
            $this->formMap->addPropertyMap(new Form\Text('email'));
            $this->formMap->addPropertyMap(new Form\Text('notes'));
            $this->formMap->addPropertyMap(new Form\Boolean('active'));
        }
        return $this->formMap;
    }

    /**
     *
     * @param $hash
     * @param int $institutionId
     * @param string|array $role
     * @return Institution|null|Model
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
        $res = $this->select($where);
        return $res->current();
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
     * @throws \Tk\Db\Exception
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

        if (!empty($filter['subjectId'])) {
            $from .= sprintf(', subject_has_user c');
            $where .= sprintf('a.id = c.user_id AND c.subject_id = %d AND ', (int)$filter['subjectId']);
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