<?php
namespace App\Db;

use Tk\Db\Tool;
use Tk\Db\Map\ArrayObject;
use Tk\DataMap\Db;
use Tk\DataMap\Form;

/**
 * Class CourseMap
 *
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
            $this->dbMap->addProperty(new Db\Number('id'), 'key');
            $this->dbMap->addProperty(new Db\Number('ownerId', 'owner_id'));
            $this->dbMap->addProperty(new Db\Text('name'));
            $this->dbMap->addProperty(new Db\Text('domain'));
            $this->dbMap->addProperty(new Db\Text('email'));
            $this->dbMap->addProperty(new Db\Text('description'));
            $this->dbMap->addProperty(new Db\Text('logo'));
            $this->dbMap->addProperty(new Db\Boolean('active'));
            $this->dbMap->addProperty(new Db\Text('hash'));
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
            $this->formMap->addProperty(new Form\Number('ownerId'));
            $this->formMap->addProperty(new Form\Text('name'));
            $this->formMap->addProperty(new Form\Text('domain'));
            $this->formMap->addProperty(new Form\Text('email'));
            $this->formMap->addProperty(new Form\Text('description'));
            $this->formMap->addProperty(new Form\Text('logo'));
            $this->formMap->addProperty(new Form\Boolean('active'));

            $this->setPrimaryKey($this->formMap->currentProperty('key')->getColumnName());
        }
        return $this->formMap;
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
     * @param int $userId
     * @return Institution
     */
    public function findByOwnerId($userId)
    {
        $where = sprintf('owner_id = %s', (int)$userId);
        return $this->select($where)->current();
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
     * Check if
     *
     * @param $consumer_key256
     * @return bool
     */
    public function ltiKeyExists($consumer_key256, $ignoreId = 0)
    {
        $sql = sprintf('SELECT * FROM %slti2_consumer WHERE consumer_key256 = %s', \App\Factory::$LTI_DB_PREFIX, $this->getDb()->quote($consumer_key256));
        //$sql = sprintf('SELECT * FROM lti2_consumer WHERE consumer_key256 = %s', $this->getDb()->quote($consumer_key256));
        if ($ignoreId) {
            $sql .= sprintf(' AND consumer_pk != %s ', (int)$ignoreId);
        }
        return ($this->getDb()->query($sql)->rowCount() > 0);
    }




}

