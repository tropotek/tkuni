<?php
namespace App\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
class Mirror extends Iface
{


    /**
     *
     */
    protected function configure()
    {
        $this->setName('mirror')
            ->addOption('withData', 'd', InputOption::VALUE_NONE, 'Use scp to copy the data folder from the live site.')
            ->addOption('noSql', 'S', InputOption::VALUE_NONE, 'Do not execute the sql component of the mirror')
            ->setDescription('Mirror the data and files from the Live site');
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
        if (!$config->isDebug()) {
            $this->writeError('Error: Only run this command in a debug environment.');
            return;
        }
        $thisDb = $config->getDb();
        $liveDb = null;
        if (is_array($config->get('live.db'))) {
            $liveDb = \Tk\Db\Pdo::create($config->get('live.db'));
        }
        if (!$liveDb) {
            $this->writeError('Error: No source DB connection params available.');
            return;
        }

        $debugSqlFile = $config->getSitePath() . '/bin/assets/debug.sql';
        $thisSqlFile = $config->getTempPath() . '/tmpt.sql';
        $liveSqlFile = $config->getTempPath() . '/tmpl.sql';
        $liveBackup = \Tk\Util\SqlBackup::create($liveDb);
        $thisBackup = \Tk\Util\SqlBackup::create($thisDb);
        $exclude = array(\Tk\Session\Adapter\Database::$DB_TABLE);


        // Copy the data from the live DB
        if (!$input->getOption('noSql')) {
            $this->write('Backup live.DB to file');
            $liveBackup->save($liveSqlFile, array('exclude' => $exclude));
            // Prevent accidental writing to live DB
            $liveBackup = null;
            $this->write('Backup this.DB to file');
            $thisBackup->save($thisSqlFile, array('exclude' => $exclude));
            $this->write('Drop this.DB tables/views');
            $thisDb->dropAllTables(true, $exclude);
            $this->write('Import live.DB file to this.DB');
            $thisBackup->restore($liveSqlFile);
            $this->write('Apply dev sql updates');
            $thisBackup->restore($debugSqlFile);
            unlink($liveSqlFile);
            unlink($thisSqlFile);

        }

        // if withData, copy the data folder and its files
        if ($input->getOption('withData')) {
            if (!$config->get('live.data.path')) {
                $this->writeError('Error: Cannot copy data files as the live.data.path is not configured.');
                return;
            }

            $dataPath = $config->getDataPath();
            $tmpPath = $dataPath . '_tmp';
            $bakPath = $dataPath . '_bak';

            if (is_dir($tmpPath)) { // Delete any tmpPath if exists
                $cmd = sprintf('rm -rf %s ', escapeshellarg($tmpPath));
                system($cmd);
            }
            if (!is_dir($tmpPath))
                mkdir($tmpPath, 0777, true);

            $this->write('Copy live data files.');
            $livePath = rtrim($config->get('live.data.path'), '/') . '/*';
            $cmd = sprintf('scp -r %s %s ', escapeshellarg($livePath), escapeshellarg($tmpPath));
            $this->write($cmd);
            system($cmd);

            if (is_dir($bakPath)) { // Remove old bak data
                $cmd = sprintf('rm -rf %s ', escapeshellarg($bakPath));
                system($cmd);
            }
            if (is_dir($dataPath)) {    // Move existing data to data_bak
                $this->write('Move current data files.');
                $cmd = sprintf('mv %s %s ', escapeshellarg($dataPath), escapeshellarg($bakPath));
                $this->write($cmd);
                system($cmd);
            }
            if (is_dir($dataPath)) {    // Move temp data to data
                $this->write('Finalise new data files.');
                $cmd = sprintf('mv %s %s ', escapeshellarg($tmpPath), escapeshellarg($dataPath));
                $this->write($cmd);
                system($cmd);
            }

            // use scp to copy the data files
            $this->write('Change data folder permissions');
            if (is_dir($dataPath)) {
                $cmd = sprintf('chmod ug+rw %s -R', escapeshellarg($dataPath));
                $this->write($cmd);
                system($cmd);
                $cmd = sprintf('chgrp www-data %s -R', escapeshellarg($dataPath));
                $this->write($cmd);
                system($cmd);
            }
        }

        $this->write('Complete!!!');

    }



}
