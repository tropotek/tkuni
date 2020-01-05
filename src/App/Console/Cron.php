<?php
namespace App\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tk\Config;

/**
 * Cron job to be run nightly
 *
 * # run Nightly site cron job
 *   0  4,16  *   *   *      php /home/user/public_html/bin/cmd cron > /dev/null 2>&1
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
class Cron extends \Bs\Console\Iface
{

    /**
     *
     */
    protected function configure()
    {
        $this->setName('cron')
            ->setDescription(
                sprintf('Run the site cron script:     "*/10  *   *   *   *      php %s/bin/cmd cron > /dev/null 2>&1"',
                    Config::getInstance()->getSrcPath())
            );
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

        $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);


        $this->write('Cron Script Executed...', OutputInterface::VERBOSITY_VERBOSE);


    }

}
