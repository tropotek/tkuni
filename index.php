<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

include(dirname(__FILE__) . '/_prepend.php');

$request = \App\Factory::getRequest();
$kernel = \App\Factory::getFrontController();

$response = $kernel->handle($request)->send();
$kernel->terminate($request, $response);


