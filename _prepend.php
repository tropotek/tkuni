<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

if (!isset($sitePath)) $sitePath = dirname(__FILE__);
if (!isset($siteUrl)) $siteUrl = null;

/** @var \Composer\Autoload\ClassLoader $composer */
$composer = include($sitePath . '/vendor/autoload.php');

$config = \App\Config::getInstance($siteUrl, $sitePath);
\App\Bootstrap::execute();

$config->set('composer', $composer);
