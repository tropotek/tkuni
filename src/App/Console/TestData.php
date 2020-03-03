<?php
namespace App\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Uni\Db\Permission;
use Uni\Db\User;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
class TestData extends \Bs\Console\TestData
{

    /**
     *
     */
    protected function configure()
    {
        $this->setName('testData')
            ->setAliases(array('td'))
            ->setDescription('Fill the database with test data');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        // required vars
        $config = \App\Config::getInstance();
        $db = $this->getConfig()->getDb();

        if (!$config->isDebug()) {
            $this->writeError('Error: Only run this command in a debug environment.');
            return;
        }

        /** @var \Uni\Db\Institution $institution */
        $institution = $config->getInstitutionMapper()->find(1);

        $db->exec('DELETE FROM `user` WHERE `notes` = \'***\' ');
        for($i = 0; $i < 25; $i++) {
            $user = $config->createUser();
            $user->setInstitutionId($institution->getId());
            $user->setName($this->createFullName());
            do {
                $user->setUsername(strtolower($this->createName()) . '.' . rand(1000, 10000000));
            } while($config->getUserMapper()->findByUsername($user->getUsername()) != null);
            $user->setEmail($this->createUniqueEmail());
            $user->setType((rand(1, 10) <= 5) ? \Uni\Db\User::TYPE_STAFF : \Uni\Db\User::TYPE_STUDENT);
            $user->setNotes('***');
            $user->save();
            $user->setNewPassword('password');
            $user->save();

            $user->addPermission(\Uni\Db\Permission::getDefaultPermissionList($user->getType()));
            if ($user->isStaff() && (rand(1, 10) <= 5)) {
                $user->addPermission(Permission::IS_COORDINATOR);
            }
            if ($user->isStaff() && (rand(1, 10) <= 8)) {
                $user->addPermission(Permission::IS_LECTURER);
            }
            if ($user->isStaff() && (rand(1, 10) <= 4)) {
                $user->addPermission(Permission::IS_MENTOR);
            }
        }


        $db->exec('TRUNCATE `course`');
        $db->exec('TRUNCATE `subject`');
        $db->exec('TRUNCATE `subject_has_user`');
        $db->exec('TRUNCATE `course_has_user`');

        for ($i = 0; $i < 4; $i++) {
            $year = 2016 + $i;
            $course = $config->createCourse();
            $course->setInstitutionId($institution->getId());
            /** @var User $coordinator */
            $coordinator = $config->getUserMapper()->findFiltered(array(
                'type' => array(\Uni\Db\User::TYPE_STAFF),
                'permission' => Permission::IS_COORDINATOR
            ), \Tk\Db\Tool::create('RAND()', 1))->current();
            if ($coordinator) {
                $course->setCoordinatorId($coordinator->getId());
            }
            $course->setName('Test Course #'.$i);
            $course->setCode('TEST10001');
            $course->setEmail($institution->getEmail());
            $course->save();
            if ($coordinator) {
                $course->addUser($coordinator);
            }
            $list = $config->getUserMapper()->findFiltered(array(
                'type' => array(\Uni\Db\User::TYPE_STAFF)
            ));
            foreach ($list as $user) {
                $course->addUser($user);
            }

            for ($j = 0; $j < 4; $j++) {
                $year = 2018 + $j;
                $subject = $config->createSubject();
                $subject->setCourseId($course->getId());
                $subject->setInstitutionId($institution->getId());
                $subject->setName($course->getName() . ' - ' . $year);
                $subject->setCode($course->getCode() . '_' . $year);
                $subject->setEmail($course->getCoordinator()->getEmail());
                $subject->setDateStart(\Tk\Date::floor()->setDate($year, 1, 1));
                $subject->setDateEnd(\Tk\Date::ceil()->setDate($year, 12, 31));
                $subject->save();

                $list = $config->getUserMapper()->findFiltered(array(
                    'type' => array(\Uni\Db\User::TYPE_STUDENT)
                ));
                foreach ($list as $user) {
                    $subject->addUser($user);
                }

            }

        }

        $db->exec('TRUNCATE `user_mentor`');
        $mentorList = $config->getUserMapper()->findFiltered(array(
            'institutionId' => $institution->getId(),
            'type' => array(\Uni\Db\User::TYPE_STAFF),
            'permission' => Permission::IS_MENTOR
        ), \Tk\Db\Tool::create('RAND()'));
        foreach ($mentorList as $mentor) {
            $studentList = $config->getUserMapper()->findFiltered(array(
                'institutionId' => $institution->getId(),
                'type' => array(\Uni\Db\User::TYPE_STUDENT)
            ), \Tk\Db\Tool::create('RAND()', 8));
            foreach ($studentList as $student) {
                $config->getUserMapper()->addMentor($mentor->getId(), $student->getId());
            }
        }
    }


    public function getExampleSql()
    {
        $sql = <<<SQL
-- ----------------------------
--  TEST DATA
-- ----------------------------

INSERT INTO institution (user_id, name, email, phone, domain, description, logo, feature, street, city, state, postcode, country, address, map_lat, map_lng, map_zoom, active, del, hash, modified, created) VALUES
(2, 'The University Of Melbourne', 'admin@unimelb.edu.au', '', '', '<p>The University Of Melbourne</p>', '', '', '250 Princes Highway', 'Werribee', 'Victoria', '3030', 'Australia', '250 Princes Hwy, Werribee VIC 3030, Australia', -37.88916600, 144.69314774, 18.00, 1, 0, MD5('1'), NOW(), NOW())
;

INSERT INTO `user` (`role_id`, `institution_id`, `username`, `password` ,`name_first`, `name_last`, `email`, `active`, `hash`, `modified`, `created`)
VALUES
  (1, 0, 'admin', MD5(CONCAT('password', MD5('10admin'))), 'Administrator', '', 'admin@example.com', 1, MD5('10admin'), NOW(), NOW()),
  (2, 0, 'unimelb', MD5(CONCAT('password', MD5('20unimelb'))), 'The University Of Melbourne', '', 'fvas@unimelb.edu.au', 1, MD5('20unimelb'), NOW(), NOW()),
  (5, 1, 'staff', MD5(CONCAT('password', MD5('31staff'))), 'Staff', 'Unimelb', 'staff@unimelb.edu.au', 1, MD5('31staff'), NOW(), NOW()),
  (4, 1, 'student', MD5(CONCAT('password', MD5('41student'))), 'Student', 'Unimelb', 'student@unimelb.edu.au', 1, MD5('41student'), NOW(), NOW())
;

INSERT INTO `subject` (`institution_id`, `course_id`, `name`, `code`, `email`, `description`, `date_start`, `date_end`, `modified`, `created`)
  VALUES (1, 1, 'Poultry Test Field Work', 'VETS50001_2014_SM1', 'subject@unimelb.edu.au', '',  NOW(), DATE_ADD(NOW(), INTERVAL 190 DAY), NOW(), NOW() )
--  VALUES (1, 'Poultry Industry Field Work', 'VETS50001_2014_SM1', 'subject@unimelb.edu.au', '',  NOW(), DATE_ADD(CURRENT_DATETIME, INTERVAL 190 DAY), NOW(), NOW() )
;

INSERT INTO `subject_has_user` (`user_id`, `subject_id`)
VALUES
  (4, 1)
;

INSERT INTO `course_has_user` (`user_id`, `course_id`)
VALUES
  (3, 1)
;

# INSERT INTO `subject_pre_enrollment` (`subject_id`, `email`)
# VALUES
#   (1, 'student@unimelb.edu.au')
# ;


SQL;

    }



}
