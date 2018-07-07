<?php
namespace App\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
class Upgrade extends Iface
{

    /**
     *
     */
    protected function configure()
    {
        $this->setName('upgrade')
            ->setAliases(array('ug'))
            ->setDescription('Call this to upgrade the site from git and update its dependencies');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $config = \App\Config::getInstance();
        if ($config->isDebug()) {
            $this->writeError('Error: Only run this command in a live environment.');
            return;
        }
        
        $cmdList = array(
            'git reset --hard',
            'git checkout master',
            'git pull',
            'git log --tags --simplify-by-decoration --pretty="format:%ci %d %h"',
            'git checkout {tag}',
            'composer update'
        );

        if ($config->isDebug()) {
            array_unshift($cmdList, 'ci');
            $cmdList[] = 'git reset --hard';
            $cmdList[] = 'git checkout master';
            $cmdList[] = 'composer update';
        }


        $tag = '';
        $output = array();
        foreach ($cmdList as $i => $cmd) {
            unset($output);
            if (preg_match('/^git log /', $cmd)) {      // find tag version
                exec($cmd . ' 2>&1', $output, $ret);
                foreach ($output as $line) {
                    if (preg_match('/\(tag\: ([0-9\.]+)\)/', $line, $regs)) {
                        $tag = $regs[1];
                        break;
                    }
                }
                if (!$tag) {
                    $this->writeError('Error: Cannot find version tag.');
                    return;
                }
            } else {
                if ($tag) {
                    $cmd = str_replace('{tag}', $tag, $cmd);
                }
                $this->writeInfo($cmd);
                if (preg_match('/^composer /', $cmd)) {
                    system($cmd);
                } else {
                    exec($cmd . ' 2>&1', $output, $ret);
                    if ($cmd == 'ci') {
                        continue;
                    }
                    $this->write('  ' . implode("\n  ", $output));
                }
            }
        }

    }

}
