<?php
namespace App;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;


/**
 * Class Bootstrap
 *
 * This should be called to setup the App lib environment
 *
 * ~~~php
 *     \App\Bootstrap::execute();
 * ~~~
 *
 * I am using the composer.json file to auto execute this file using the following entry:
 *
 * ~~~json
 *   "autoload":  {
 *     "psr-0":  {
 *       "":  [
 *         "src/"
 *       ]
 *     },
 *     "files" : [
 *       "src/App/Bootstrap.php"    <-- This one
 *     ]
 *   }
 * ~~~
 *
 *
 * @author Michael Mifsud <info@tropotek.com>  
 * @link http://www.tropotek.com/  
 * @license Copyright 2015 Michael Mifsud  
 */
class Bootstrap
{

    /**
     * This will also load dependant objects into the config, so this is the DI object for now.
     *
     * @throws \Exception
     */
    static function execute()
    {
        if (version_compare(phpversion(), '5.4.0', '<')) {
            // php version must be high enough to support traits
            throw new \Exception('Your PHP5 version must be greater than 5.4.0 [Curr Ver: '.phpversion().']');
        }

        $config = \App\Config::create();
        include($config->getSrcPath() . '/config/application.php');

        // This maybe should be created in a Factory or DI Container....
        if (is_readable($config->getLogPath()) && !$config->getRequest()->has('nolog')) {
            $logger = new Logger('system');
            $handler = new StreamHandler($config->getLogPath(), $config->getLogLevel());
            $formatter = new \Tk\Log\MonologLineFormatter();
            $formatter->setScriptTime($config->getScriptTime());
            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);
            $config->setLog($logger);
            \Tk\Log::getInstance($logger);
        } else {
            var_dump('Log path not readable: ' . $config->getLogPath());
            exit;
        }

        if (!$config->isDebug()) {
            ini_set('display_errors', 'Off');
            error_reporting(0);
        } else {
            \Dom\Template::$enableTracer = true;
        }

        // * Logger [use error_log()]
        \Tk\ErrorHandler::getInstance($config->getLog());

        // Initiate the default database connection
        $config->getDb();
        $config->replace(\Tk\Db\Data::create()->all());


        // Return if using cli (Command Line)
        if ($config->isCli()) return $config;

        // --- HTTP only bootstrapping from here ---

        // Include all URL routes
        include($config->getSrcPath() . '/config/routes.php');

        // * Session
        $config->getSession();
        return $config;
    }

}

// called by autoloader, see composer.json -> "autoload" : files []...
try {
    Bootstrap::execute();
} catch (\Exception $e) {
}

