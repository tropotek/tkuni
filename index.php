<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

$appPath = __DIR__;
include($appPath . '/vendor/autoload.php');

$config = \Tk\Config::getInstance();

$kernel = new \App\FrontController(\App\Factory::getEventDispatcher(), \App\Factory::getControllerResolver(), $config);
$response = $kernel->handle($config->getRequest())->send();
$kernel->terminate($config->getRequest(), $response);

//$kernel->terminate($config->getRequest(), new Response());

/**
// Enable Cache
$kernel = new HttpCache($kernel, new Store($config->getCachePath()));
$kernel->handle($config->getRequest())->send();
$kernel->terminate($config->getRequest(), new Response());
*/

