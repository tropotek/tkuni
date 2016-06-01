<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

$appPath = __DIR__;
include($appPath . '/vendor/autoload.php');

$request = \App\Factory::getRequest();
$kernel = \App\Factory::getFrontController();

$response = $kernel->handle($request)->send();
$kernel->terminate($request, $response);

