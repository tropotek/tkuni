<?php
namespace App\Composer;

use Composer\Script\Event;
use Tk\Config;
use Tk\Db\Pdo;
use Tk\Util\SqlMigrate;

/**
 * Class InitProject
 *
 * Default initProject installer class for the framework V2
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
class InitProjectEvent
{

    /**
     * @param Event $event
     */
    static function postInstall(Event $event)
    {
        self::init($event, true);
    }

    /**
     * @param Event $event
     */
    static function postUpdate(Event $event)
    {
        self::init($event, false);
    }

    static function vd($obj)
    {
        echo print_r($obj, true) . "\n";
    }

    /**
     * @param Event $event
     * @param bool $isInstall
     */
    static function init(Event $event, $isInstall = false)
    {
        try {
            $sitePath = $_SERVER['PWD'];
            $io = $event->getIO();
            $composer = $event->getComposer();
            $pkg = $composer->getPackage();

            // Get the PHP user that will be executing the scripts
            if (function_exists('posix_getpwuid')) {
                $a = posix_getpwuid(fileowner(__FILE__));
                $phpUser = $a['dir'];
            } else {
                $phpUser = `whoami`;
            }

            $name = substr($pkg->getName(), strrpos($pkg->getName(), '/')+1);
            $version = $pkg->getVersion();
            $releaseDate = $pkg->getReleaseDate()->format('Y-m-d H:i:s');
            $year = $pkg->getReleaseDate()->format('Y');
            $desc = wordwrap($pkg->getDescription(), 45, "\n               ");
            $authors =  [];
            foreach ($pkg->getAuthors() as $auth) {
                $authors[] = $auth['name'];
            }
            $authors = implode(', ', $authors);

            $head = <<<STR
-----------------------------------------------------------
       $name Installer - (c) tropotek.com $year
----------------------------------------------------------- 
  Project:     $name
  Version:     $version
  Released:    $releaseDate
  Author:      $authors
  Description: $desc
-----------------------------------------------------------
STR;
            $io->write(self::bold($head));
            $configInFile = $sitePath . '/src/config/config.php.in';
            $configFile = $sitePath . '/src/config/config.php';
            $htInFile = $sitePath . '/.htaccess.in';
            $htFile = $sitePath . '/.htaccess';


            // Check existing config file
            $overwrite = false; // Overwrite the existing Config if it exists
            if (@is_file($configInFile)) {
                if ($isInstall && is_file($configFile)) {
                    $overwrite = $io->askConfirmation(self::warning('Do you want to replace the existing site configuration [N]: '), false);
                }
                if (!is_file($configFile)) {
                    $overwrite = true;
                }
            }

            // Create new config.php
            if ($overwrite) {
                $configContents = file_get_contents($configInFile);
                $io->write(self::green('Please answer the following questions to setup your new site configuration.'));
                $configVars = self::userInput($io);

                // Set dev/debug mode
                if ($composer->getPackage()->isDev()) {
                    $configVars['debug'] = 'true';
                    $logPath = '/home/user/log/error.log';
                    if (!empty($phpUser)) {
                        $logPath = $phpUser . '/log/error.log';
                    }
                    $configVars['system.log.path'] = $logPath;
                }

                // update the config contents string
                foreach ($configVars as $k => $v) {
                    $configContents = self::setConfigValue($k, self::quote($v), $configContents);
                }

                $io->write(self::green('Saving config.php'));
                file_put_contents($configFile, $configContents);
            }

            // Create .htaccess
            if (@is_file($htInFile)) {
                if ($overwrite || !@is_file($htFile)) {
                    $io->write(self::green('Creating .htaccess file'));
                    copy($htInFile, $htFile);
                    $path = '/';
                    if (preg_match('/(.+)\/public_html\/(.*)/', $sitePath, $regs)) {
                        $user = basename($regs[1]);
                        $path = '/~' . $user . '/' . $regs[2] . '/';
                    }
                    $path = trim($io->ask(self::bold('What is the base URL path [' . $path . ']: '), $path));
                    if (!$path) $path = '/';
                    $io->write(self::green('Saving .htaccess file'));
                    $buf = file_get_contents($htFile);
                    $buf = str_replace('RewriteBase /', 'RewriteBase ' . $path, $buf);
                    file_put_contents($htFile, $buf);
                }
            }

            // Create data dir. TODO: Path could be created by user???
            if (!is_dir($sitePath . '/data')) {
                $io->write(self::green('Creating data directory `./data`'));
                mkdir($sitePath . '/data', 0777, true);
            }

            // Migrate database.
            if ($isInstall) {
                include $configFile;
                $config = Config::getInstance();

                $db = Pdo::getInstance($config['db.name'], $config->getGroup('db'));
                $tables = $db->getTableList();
                $overwrite = false;
                if (count($tables)) {
                    $overwrite = $io->askConfirmation(self::warning('Do you wish to remove the existing database tables [N]: '), false);
                }
                if ($overwrite) {
                    $db->dropAllTables(true);

                    $io->write(self::green('Installing Site Database'));
                    $migrate = new SqlMigrate($db, $config->getSitePath());
                    $migrate->setTmpPath($config->getTempPath());
                    $files = $migrate->migrate($config->getSrcPath() . '/config/sql');
                    foreach ($files as $f) {
                        $io->write(self::green('  .' . $f));
                    }
                    $io->write(self::green('As this is a new DB install login into the site using User: `admin` and Password: `password` and configure your site as needed.'));
                }
            }
        } catch (\Exception $e) {
            $io->write(self::red($e->__toString()));
        }

    }

    /**
     * @param Composer\IO\IOInterface $io
     * @return array
     */
    static function userInput($io)
    {
        $config = [];
        // Prompt for the database access
        $dbTypes = ['mysql', 'pgsql', 'sqlite'];
        $io->write('<options=bold>');
        $i = $io->select('Select the DB type [mysql]: ', $dbTypes, 0);
        $io->write('</>');
        $config['db.type'] = $dbTypes[$i];

        $config['db.host'] = $io->ask(self::bold('Set the DB hostname [localhost]: '), 'localhost');
        $config['db.name'] = $io->askAndValidate(self::bold('Set the DB name: '), function ($data) { if (!$data) throw new \Exception('Please enter the DB name to use.');  return $data; });
        $config['db.user'] = $io->askAndValidate(self::bold('Set the DB user: '), function ($data) { if (!$data) throw new \Exception('Please enter the DB username.'); return $data; });
        $config['db.pass'] = $io->askAndValidate(self::bold('Set the DB password: '), function ($data) { if (!$data) throw new \Exception('Please enter the DB password.'); return $data; });

        return $config;
    }


    /**
     * updateConfig
     *
     * @param string $k
     * @param string $v
     * @param string $configContents
     * @return mixed
     */
    static function setConfigValue($k, $v, $configContents)
    {
        $reg = '/\$config\[[\'"]('.preg_quote($k, '/').')[\'"]\]\s=\s[\'"]?(.+)[\'"]?;/';
        return preg_replace($reg, '\$config[\'$1\'] = ' . $v . ';', $configContents);
    }

    static function bold($str) { return '<options=bold>'.$str.'</>'; }

    static function green($str) { return '<fg=green>'.$str.'</>'; }

    static function warning($str) { return '<fg=yellow;options=bold>'.$str.'</>'; }

    static function red($str) { return '<fg=white;bg=red>'.$str.'</>'; }

    static function quote($str) { return '\''.$str.'\''; }

// IO Examples
//$output->writeln('<fg=green>foo</>');
//$output->writeln('<fg=black;bg=cyan>foo</>');
//$output->writeln('<bg=yellow;options=bold>foo</>');

}