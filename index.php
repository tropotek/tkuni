<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
try {
    include(dirname(__FILE__) . '/_prepend.php');
    $config = \App\Config::getInstance();
    $request = $config->getRequest();
    $fc = $config->getFrontController();
    $response = $fc->handle($request)->send();
    $fc->terminate($request, $response);
} catch (Exception $e) {
    // TODO:
    \Tk\Log::error($e->__toString());
}


