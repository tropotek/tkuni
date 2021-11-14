<?php
namespace App\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tk\Config;
use Tk\Db\Data;


/**
 * Cron job to be run nightly
 *
 * # run Nightly site cron job
 *   * /5  *  *   *   *      php /home/user/public_html/bin/cmd cron > /dev/null 2>&1
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
        $path = getcwd();
        $this->setName('cron')
            ->setDescription('The site cron script. crontab line: */1 *  * * *   ' . $path . '/bin/cmd cron > /dev/null 2>&1');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setInput($input);
        $this->setOutput($output);
        if ($this->getConfig()->get('site.maintenance.enabled')) {
            return;
        }

        // Timed runtimes
        // TODO: uncomment required items
        $times = [
            'cron.last.now' => 1,
            //'cron.last.5min' => 60 * 5,
            //'cron.last.10min' => 60 * 10,
            //'cron.last.30min' => 60 * 30,
            //'cron.last.hour' => 60 * 60,
            //'cron.last.day' => 60 * 60 * 24,
            //'cron.last.week' => 60 * 60 * 24 * 7
        ];

        $data = Data::create();
        $now = \Tk\Date::create();
        foreach ($times as $k => $v) {
            $last = $data->get($k, null);
            if ($last)  $last = \Tk\Date::create($last, $now->getTimezone());
            $interval = $now->getTimestamp() - $last->getTimestamp();
            if (!$last || $interval >= $v) {
                $data->set($k, $now->getTimestamp())->save();
                \Tk\Log::warning($k . ' Executed:');
                $a = explode('.', $k);
                $func = 'exec'.ucfirst(end($a));
                if (method_exists($this, $func)) {
                    try {
                        $this->$func();
                    } catch (\Exception $e) { vd($e->__toString()); }
                }
                $this->execHour();
            } else {
                \Tk\Log::debug($k . ' will run in : ' . ($v-$interval) . 'sec');
            }
        }

    }



    protected function execNow()
    {
        try {

        } catch (\Exception $e) { $this->writeError($e->__toString()); }
    }

    protected function exec5min()
    {
        try {

        } catch (\Exception $e) { $this->writeError($e->__toString()); }
    }

    protected function exec10min()
    {
        try {

        } catch (\Exception $e) { $this->writeError($e->__toString()); }
    }

    protected function exec30min()
    {
        try {
        } catch (\Exception $e) { $this->writeError($e->__toString()); }
    }

    protected function execHour()
    {
        try {
        } catch (\Exception $e) { $this->writeError($e->__toString()); }
    }

    protected function execDay()
    {
        try {
        } catch (\Exception $e) { $this->writeError($e->__toString()); }
    }

    protected function execWeek()
    {
        try {
        } catch (\Exception $e) { $this->writeError($e->__toString()); }
    }

    /* *********************************************** */




}
