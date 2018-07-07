<?php
namespace App\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
class Debug extends Iface
{

    /**
     *
     */
    protected function configure()
    {
        $this->setName('debug')
            ->setDescription('(Debug) Setup the App for the development environment');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Tk\Db\Exception
     * @throws \Tk\Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $config = \App\Config::getInstance();
        $db = $config->getDb();

        if (!$config->isDebug()) {
            $this->writeError('Error: Only run this command in a debug environment.');
            return;
        }

        $debugSql = $config->getSitePath().'/bin/assets/debug.sql';
        $bak = new \Tk\Util\SqlBackup($db);

        $this->writeComment('  - Running SQL: `bin/assets/debug.sql`');
        $bak->restore($debugSql);

    }

}
