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

$config['system.theme.admin']   = $config['system.template.path'] . '/admin';
$config['system.theme.public']  = $config['system.template.path'] . '/public';

$config['template.admin']       = $config['system.theme.admin']  . '/admin.html';
$config['template.lti']         = $config['system.theme.admin']  . '/lti.html';
$config['template.client']      = $config['system.theme.admin']  . '/admin.html';
$config['template.staff']       = $config['system.theme.admin']  . '/admin.html';
$config['template.student']     = $config['system.theme.admin']  . '/admin.html';
$config['template.public']      = $config['system.theme.public'] . '/public.html';

$config['template.login']       = $config['system.theme.admin']  . '/login.html';

/*
 * Set the error page template
 */
$config['template.error']           = $config['system.template.path']   . '/theme-cube/error.html';

/*
 * Set the maintenance page template
 */
$config['template.maintenance']     = $config['system.template.path']   . '/theme-cube/maintenance.html';


/*
 * Enable logging of triggered events
 * Default: false
 */
//$config['event.dispatcher.log'] = true;

//$config['google.map.apikey'] = '';
//$config['google.recaptcha.publicKey'] = '';
//$config['google.recaptcha.privateKey'] = '';



/* **********************************************
 *  Common URL for the base controllers
 * **********************************************/
//$config['url.auth.home'] = '/index.html';
//$config['url.auth.login'] = '/login.html';
//$config['url.auth.logout'] = '/logout.html';
//$config['url.auth.register'] = '/register.html';
//$config['url.auth.recover'] = '/recover.html';
