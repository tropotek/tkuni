<?php
namespace App\Db;

use Tk\Db\Tool;
use Tk\Db\Map\ArrayObject;
use Tk\DataMap\Db;
use Tk\DataMap\Form;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class InstitutionMap extends Mapper
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
            $this->dbMap->addPropertyMap(new Db\Integer('userId', 'user_id'));
            $this->dbMap->addPropertyMap(new Db\Text('name'));
            $this->dbMap->addPropertyMap(new Db\Text('domain'));
            $this->dbMap->addPropertyMap(new Db\Text('email'));
            $this->dbMap->addPropertyMap(new Db\Text('description'));
            $this->dbMap->addPropertyMap(new Db\Text('logo'));
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
            $this->formMap->addPropertyMap(new Form\Integer('userId'));
            $this->formMap->addPropertyMap(new Form\Text('name'));
            $this->formMap->addPropertyMap(new Form\Text('domain'));
            $this->formMap->addPropertyMap(new Form\Text('email'));
            $this->formMap->addPropertyMap(new Form\Text('description'));
            $this->formMap->addPropertyMap(new Form\Text('logo'));
            $this->formMap->addPropertyMap(new Form\Boolean('active'));
        }
        return $this->formMap;
    }


    /**
     *
     * @param null|\Tk\Db\Tool $tool
     * @return ArrayObject|Institution[]
     * @throws \Tk\Db\Exception
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
     * @return \Tk\Db\Map\Model|Institution
     * @throws \Tk\Db\Exception
     */
    public function findByhash($hash, $active = 1)
    {
        $where = sprintf('hash = %s AND active = %s', $this->getDb()->quote($hash), (int)$active);
        return $this->select($where)->current();
    }

    /**
     *
     * @param $domain
     * @return \Tk\Db\Map\Model|Institution
     * @throws \Tk\Db\Exception
     */
    public function findByDomain($domain)
    {
        $where = sprintf('domain = %s', $this->getDb()->quote($domain));
        return $this->select($where)->current();
    }

    /**
     *
     * @param int $userId
     * @return \Tk\Db\Map\Model|Institution
     * @throws \Tk\Db\Exception
     */
    public function findByUserId($userId)
    {
        $where = sprintf('user_id = %s', (int)$userId);
        return $this->select($where)->current();
    }

    /**
     * Find filtered records
     *
     * @param array $filter
     * @param Tool $tool
     * @return ArrayObject|Institution[]
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

        if (!empty($filter['userId'])) {
            $where .= sprintf('a.user_id = %s AND ', (int)$filter['userId']);
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

