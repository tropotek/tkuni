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
class SubjectMap extends Mapper
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
            $this->dbMap->addPropertyMap(new Db\Text('name'));
            $this->dbMap->addPropertyMap(new Db\Text('code'));
            $this->dbMap->addPropertyMap(new Db\Text('email'));
            $this->dbMap->addPropertyMap(new Db\Text('description'));
            $this->dbMap->addPropertyMap(new Db\Date('dateStart', 'date_start'));
            $this->dbMap->addPropertyMap(new Db\Date('dateEnd', 'date_end'));
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
            $this->formMap->addPropertyMap(new Form\Text('name'));
            $this->formMap->addPropertyMap(new Form\Text('code'));
            $this->formMap->addPropertyMap(new Form\Text('email'));
            $this->formMap->addPropertyMap(new Form\Text('description'));
            $this->formMap->addPropertyMap(new Form\Date('dateStart'));
            $this->formMap->addPropertyMap(new Form\Date('dateEnd'));
        }
        return $this->formMap;
    }


    /**
     *
     * @param string $code
     * @param int $institutionId
     * @return Subject|\Tk\Db\ModelInterface
     */
    public function findByCode($code, $institutionId)
    {
        return $this->findFiltered(array('code' => $code, 'institutionId' => $institutionId))->current();
    }

    /**
     *
     * @param int $userId
     * @param int $institutionId
     * @param Tool $tool
     * @return ArrayObject
     */
    public function findByUserId($userId, $institutionId = 0, $tool = null)
    {
        $from = sprintf('%s a, subject_has_user b', $this->getDb()->quoteParameter($this->getTable()));
        $where = sprintf('a.id = b.subject_id AND b.user_id = %d', (int)$userId);
        if ($institutionId) {
            $where .= sprintf(' AND a.institution_id = %d', (int)$institutionId);
        }
        $arr = $this->selectFrom($from, $where, $tool);
        return $arr;
    }

    /**
     *
     * @param int $institutionId
     * @param Tool $tool
     * @return ArrayObject|Subject[]
     */
    public function findActive($institutionId = 0, $tool = null)
    {
        $now = \Tk\Date::create()->format(\Tk\Date::FORMAT_ISO_DATE);
        // `now >= start && now <= finish`          =>      active
        $where = sprintf('%s >= date_start AND %s <= date_end ', $this->getDb()->quote($now), $this->getDb()->quote($now));
        if ($institutionId) {
            $where .= sprintf(' AND a.institution_id = %d', (int)$institutionId);
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

        if (!empty($filter['code'])) {
            $where .= sprintf('a.code = %s AND ', $this->getDb()->quote($filter['code']));
        }

        if (!empty($filter['email'])) {
            $where .= sprintf('a.email = %s AND ', $this->getDb()->quote($filter['email']));
        }

        if (!empty($filter['institutionId'])) {
            $where .= sprintf('a.institution_id = %s AND ', (int)$filter['institutionId']);
        }

        if (!empty($filter['userId'])) {
            $from .= sprintf(', subject_has_user b');
            $where .= sprintf('a.id = b.subject_id AND b.user_id = %s AND ', (int)$filter['userId']);
        }

        if (!empty($filter['exclude'])) {
            if (!is_array($filter['exclude'])) $filter['exclude'] = array($filter['exclude']);
            $w = '';
            foreach ($filter['exclude'] as $v) {
                $w .= sprintf('a.id != %d AND ', (int)$v);
            }
            if ($w) {
                $where .= ' ('. rtrim($w, ' AND ') . ') AND ';
            }
        }

        if ($where) {
            $where = substr($where, 0, -4);
        }

        $res = $this->selectFrom($from, $where, $tool);
        return $res;
    }




    // Enrolment direct queries - subject_has_user holds the currently enrolld users

    /**
     * @param int $subjectId
     * @param int $userId
     * @return boolean
     */
    public function hasUser($subjectId, $userId)
    {
        $stm = $this->getDb()->prepare('SELECT * FROM subject_has_user WHERE subject_id = ? AND user_id = ?');
        $stm->execute(array($subjectId, $userId));
        return ($stm->rowCount() > 0);
    }

    /**
     * @param int $subjectId
     * @param int $userId
     */
    public function addUser($subjectId, $userId)
    {
        if ($this->hasUser($subjectId, $userId)) return;
        $stm = $this->getDb()->prepare('INSERT INTO subject_has_user (user_id, subject_id)  VALUES (?, ?)');
        $stm->execute(array($subjectId, $userId));
    }

    /**
     * depending on the combination of parameters:
     *  o remove a user from a subject
     *  o remove all users from a subject
     *  o remove all subjects from a user
     *
     * @param int $subjectId
     * @param int $userId
     */
    public function removeUser($subjectId = null, $userId = null)
    {
        if ($subjectId && $userId) {
            $stm = $this->getDb()->prepare('DELETE FROM subject_has_user WHERE user_id = ? AND subject_id = ?');
            $stm->execute(array($subjectId, $userId));
        } else if(!$subjectId && $userId) {
            $stm = $this->getDb()->prepare('DELETE FROM subject_has_user WHERE user_id = ?');
            $stm->execute(array($userId));
        } else if ($subjectId && !$userId) {
            $stm = $this->getDb()->prepare('DELETE FROM subject_has_user WHERE subject_id = ?');
            $stm->execute(array($subjectId));
        }
    }




    //  Enrolment Pending List Queries - The enrollment table holds emails of users that are to be enrolled on their next login.

    /**
     * find all subjects that the user is pending enrolment
     *
     * @param $institutionId
     * @param $email
     * @param null $tool
     * @return ArrayObject|Subject[]
     */
    public function findPendingPreEnrollments($institutionId, $email, $tool = null)
    {
        $from = sprintf('%s a, %s b, %s c LEFT JOIN %s d ON (c.id = d.user_id) ',
            $this->quoteTable($this->getTable()),
            $this->quoteTable('subject_pre_enrollment'),
            $this->quoteTable('UserIface'),
            $this->quoteTable('subject_has_user'));
        $where = sprintf('a.id = b.subject_id AND b.email = c.email AND a.institution_id = %d AND b.email = %s AND d.user_id IS NULL',
            (int)$institutionId, $this->quote($email));
        return $this->selectFrom($from, $where, $tool);
    }

    /**
     * Find all pre enrolments for a subject and return with an `enrolled` boolean field
     *
     * @param $subjectId
     * @param \Tk\Db\Tool $tool
     * @return array
     */
    public function findPreEnrollments($subjectId, $tool = null)
    {
        $toolStr = '';
        if ($tool) {
            $tool->setLimit(0);
            $toolStr = ' '.$tool->toSql('', $this->getDb());
        }

        $stm = $this->getDb()->prepare('SELECT a.subject_id, a.email, a.uid, b.id as \'user_id\', IF(c.subject_id IS NULL, 0, 1) as enrolled
FROM  subject_pre_enrollment a 
  LEFT JOIN  user b ON (b.email = a.email)  
  LEFT JOIN subject_has_user c ON (b.id = c.user_id AND c.subject_id = ?)
WHERE a.subject_id = ? ' . $toolStr);
        $stm->execute(array($subjectId, $subjectId));

        $arr = $stm->fetchAll();
        return $arr;



//        $sql = sprintf('SELECT a.subject_id, a.email, a.uid, b.id as \'user_id\', IF(c.subject_id IS NULL, 0, 1) as enrolled
//FROM  %s a
//  LEFT JOIN  %s b ON (b.email = a.email)
//  LEFT JOIN %s c ON (b.id = c.user_id AND c.subject_id = %d)
//WHERE a.subject_id = %d',
//            $this->quoteTable('subject_pre_enrollment'), $this->quoteTable('user'), $this->quoteTable('subject_has_user'),
//            (int)$subjectId, (int)$subjectId);
//
//        $toolStr = '';
//        if ($tool) {
//            $tool->setLimit(0);
//            $toolStr = ' '.$tool->toSql('', $this->getDb());
//        }
//        $sql .= $toolStr;
//
//        $res = $this->getDb()->query($sql);
//        $arr = $res->fetchAll();
//        return $arr;
    }

    /**
     * Find all students on a subject pre-enrolment list
     *
     * @param $subjectId
     * @return array|\StdClass[]
     * @deprecated use findEnrolmentsBySubjectId($subjectId, $tool)
     */
    public function findAllPreEnrollments($subjectId)
    {
        $stm = $this->getDb()->prepare('SELECT * FROM user a LEFT JOIN subject_pre_enrollment b ON (a.email = b.email) WHERE b.subject_id = ?');
        $stm->execute(array($subjectId));
        return $stm->fetchAll();
    }

    /**
     * @param $subjectId
     * @param $email
     * @return bool
     */
    public function hasPreEnrollment($subjectId, $email)
    {
        $stm = $this->getDb()->prepare('SELECT * FROM subject_pre_enrollment WHERE subject_id = ? AND email = ?');
        $stm->execute(array($subjectId, $email));
        return ($stm->rowCount() > 0);
    }

    /**
     * @param int $subjectId
     * @param string $email
     * @param string $uid
     */
    public function addPreEnrollment($subjectId, $email, $uid = '')
    {
        if (!$this->hasPreEnrollment($subjectId, $email)) {
            $stm = $this->getDb()->prepare('INSERT INTO subject_pre_enrollment (subject_id, email, uid)  VALUES (?, ?, ?)');
            $stm->execute(array($subjectId, $email, $uid));
        }
        // Do not add the user to the subject_has_user table as this will be added automatically the next time the user logs in
        // This part should be implemented in a auth.onLogin listener
    }

    /**
     * @param int $subjectId
     * @param string $email
     */
    public function removePreEnrollment($subjectId, $email)
    {
        if ($this->hasPreEnrollment($subjectId, $email)) {
            $stm = $this->getDb()->prepare('DELETE FROM subject_pre_enrollment WHERE subject_id = ? AND email = ?');
            $stm->execute(array($subjectId, $email));
        }
    }

}