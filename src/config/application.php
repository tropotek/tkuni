<?php
/*
 * Application default config values
 * This file should not need to be edited
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */

$config = \App\Config::getInstance();

/**************************************
 * Default app config values
 **************************************/



/*
 * Template folders for pages
 */
$config['system.template.path'] = '/html';

$config['system.theme.public']   = $config['system.template.path'] . '/admin';
//$config['system.theme.admin']   = $config['system.template.path'] . '/admin';
$config['system.theme.admin']   = $config['system.template.path'] . '/cube';

$config['template.admin']       = $config['system.theme.admin'] . '/admin.html';
$config['template.client']      = $config['system.theme.admin'] . '/client.html';
$config['template.staff']       = $config['system.theme.admin'] . '/staff.html';
$config['template.student']     = $config['system.theme.admin'] . '/student.html';
$config['template.public']      = $config['system.theme.public'].'/public.html';

$config['template.error']       = $config['system.theme.admin'] . '/error.html';
$config['template.login']       = $config['system.theme.admin'] . '/login.html';


$config['url.auth.home'] = '/';
$config['url.auth.login'] = '/';




