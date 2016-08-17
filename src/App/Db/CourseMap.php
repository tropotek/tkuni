<?php
namespace App\Db;

use Tk\Db\Map\Model;
use Tk\Db\Tool;
use Tk\Db\Map\ArrayObject;
use Tk\DataMap\Db;
use Tk\DataMap\Form;

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
     *
     * @return \Tk\DataMap\DataMap
     */
    public function getDbMap()
    {
        if (!$this->dbMap) {
            $this->dbMap = new \Tk\DataMap\DataMap();
            $this->dbMap->addProperty(new Db\Number('id'), 'key');
            $this->dbMap->addProperty(new Db\Number('institutionId', 'institution_id'));
            $this->dbMap->addProperty(new Db\Text('name'));
            $this->dbMap->addProperty(new Db\Text('code'));
            $this->dbMap->addProperty(new Db\Text('email'));
            $this->dbMap->addProperty(new Db\Text('description'));

            $this->dbMap->addProperty(new Db\Date('start'));
            $this->dbMap->addProperty(new Db\Date('finish'));
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
            $this->formMap->addProperty(new Form\Text('name'));
            $this->formMap->addProperty(new Form\Text('code'));
            $this->formMap->addProperty(new Form\Text('email'));
            $this->formMap->addProperty(new Form\Text('description'));
            $this->formMap->addProperty(new Form\Date('start'));
            $this->formMap->addProperty(new Form\Date('finish'));

            $this->setPrimaryKey($this->formMap->currentProperty('key')->getColumnName());
        }
        return $this->formMap;
    }




    /**
     *
     * @param string $code
     * @param int $institutionId
     * @return Model
     */
    public function findByCode($code, $institutionId)
    {
        $where = sprintf('code = %s AND  institution_id = %d', $this->getDb()->quote($code), (int)$institutionId);
        return $this->select($where)->current();
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
        $from = sprintf('%s a, user_course b', $this->getDb()->quoteParameter($this->getTable()));
        $where = sprintf('a.id = b.course_id AND b.user_id = %d', (int)$userId);
        if ($institutionId) {
            $where .= sprintf(' AND a.institution_id = %d', (int)$institutionId);
        }
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
            $from .= sprintf(', user_course b');
            $where .= sprintf('a.id = b.course_id AND b.user_id = %s AND ', (int)$filter['userId']);
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
        $sql = sprintf('SELECT * FROM user_course WHERE course_id = %d AND user_id = %d', (int)$courseId, (int)$userId);
        return ($this->getDb()->query($sql)->rowCount() > 0);
    }

    /**
     * @param $courseId
     * @param $userId
     * @return \Tk\Db\PDOStatement
     */
    public function deleteUser($courseId, $userId)
    {
        $query = sprintf('DELETE FROM user_course WHERE user_id = %d AND course_id = %d', (int)$userId, (int)$courseId);
        return $this->getDb()->exec($query);
    }

    /**
     * @param $courseId
     * @param $userId
     * @return \Tk\Db\PDOStatement
     */
    public function addUser($courseId, $userId)
    {
        $query = sprintf('INSERT INTO user_course (user_id, course_id)  VALUES (%d, %d) ', (int)$userId, (int)$courseId);
        return $this->getDb()->exec($query);
    }

    /**
     * @param int $courseId
     * @return \Tk\Db\PDOStatement
     */
    public function deleteAllUsers($courseId)
    {
        $query = sprintf('DELETE FROM user_course WHERE course_id = %d ', (int)$courseId);
        return $this->getDb()->exec($query);
    }
    

    // Enrolment queries



    public function findPendingEnrollment($institutionId, $email, $tool = null)
    {
        $from = sprintf('%s a, enrollment b, %s c LEFT JOIN user_course d ON (c.id = d.user_id) ', $this->getDb()->quoteParameter($this->getTable()), $this->getDb()->quoteParameter('user'));
        $where = sprintf('a.id = b.course_id AND b.email = c.email AND a.institution_id = %d AND b.email = %s AND d.user_id IS NULL', (int)$institutionId, $this->getDb()->quote($email));

        return $this->selectFrom($from, $where, $tool);
    }

    /**
     *
     *
     * @param $courseId
     * @return array
     */
    public function findEnrollmentByCourseId($courseId, $tool = null)
    {
        $sql = sprintf('SELECT a.* FROM enrollment a LEFT JOIN %s b ON (a.email = b.email) WHERE a.course_id = %d', $this->getDb()->quoteParameter('user'), (int)$courseId);
//        if ($tool)
//            $sql .= $tool->toSql();
        $res = $this->getDb()->query($sql);
        $arr = $res->fetchAll();
        return $arr;
    }

    public function hasEnrollment($courseId, $email)
    {
        $sql = sprintf('SELECT * FROM enrollment WHERE course_id = %d AND email = %s', (int)$courseId, $this->getDb()->quote($email));
        return ($this->getDb()->query($sql)->rowCount() > 0);
    }

    /**
     * @param int $courseId
     * @param string $email
     * @param string $uid
     */
    public function enrollUser($courseId, $email, $uid = '')
    {
        if (!$this->hasEnrollment($courseId, $email)) {
            $query = sprintf('INSERT INTO enrollment (course_id, email, uid)  VALUES (%d, %s, %s) ', (int)$courseId, $this->getDb()->quote($email), $this->getDb()->quote($uid));
            $this->getDb()->exec($query);
        }
        // Do not add the user to the user_course table as this will be added automatically the next time the user logs in
        // This part should be implemented in a auth.onLogin listener
    }


    /**
     * @param int $courseId
     * @param string $email
     */
    public function unenrollUser($courseId, $email)
    {
        $query = sprintf('DELETE FROM enrollment WHERE course_id = %d AND email = %s', (int)$courseId, $this->getDb()->quote($email));
        $this->getDb()->exec($query);
        /** @var Course  $course */
        $course = CourseMap::create()->find($courseId);
        if (!$course) return;
        $user = UserMap::create()->findByEmail($email, $course->institutionId);
        if ($user) {
            $this->deleteUser($courseId, $user->id);
        }
    }





}