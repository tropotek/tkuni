<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
include(dirname(__FILE__) . '/_prepend.php');

try {
    $request = \App\Factory::getRequest();
    $kernel = \App\Factory::getFrontController();
    \Tk\Config::getInstance()->setComposer($composer);
    $response = $kernel->handle($request)->send();
    $kernel->terminate($request, $response);
} catch (Exception $e) {
    // TODO:
    \Tk\Log::error($e->__toString());
}


