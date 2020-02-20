<?php
namespace App\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
            $obj = new \Uni\Db\User();
            $obj->name = $this->createName();
            do {
                $obj->username = strtolower($this->createName()) . '.' . rand(1000, 10000000);
            } while(\Uni\Db\UserMap::create()->findByUsername($obj->username) != null);
            $obj->email = $this->createUniqueEmail();
            $obj->setType((rand(1, 10) <= 5) ? \Uni\Db\User::TYPE_STAFF : \Uni\Db\User::TYPE_STUDENT);
            $obj->notes = '***';
            $obj->save();
            $obj->setNewPassword('password');
            $obj->save();
        }


        $db->exec('TRUNCATE `subject`');
        for ($i = 0; $i < 4; $i++) {
            $year = 2016 + $i;
            $obj = new \Uni\Db\Subject();
            $obj->institutionId = $institution->getId();
            $obj->name = 'PRJ ' . $year;
            $obj->code = 'PRJ_' . $year;
            $obj->email = $institution->email;
            $obj->dateStart = \Tk\Date::floor()->setDate($year, 1, 1);
            $obj->dateEnd = \Tk\Date::ceil()->setDate($year, 12, 31);
            $obj->save();

            $list = \Uni\Db\UserMap::create()->findFiltered(array(
                'type' => array(\Uni\Db\User::TYPE_STAFF, \Uni\Db\User::TYPE_STUDENT)
            ));
            foreach ($list as $user) {
                $obj->addUser($user);
            }

        }


    }





}
