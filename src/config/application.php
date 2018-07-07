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

include_once(__DIR__ . '/session.php');

/**************************************
 * Default app config values
 **************************************/

$config['site.title'] = 'New Site';
$config['site.email'] = 'fvas-elearning@unimelb.edu.au';

$config['google.map.key']         = 'AIzaSyCSGICa1zdV6i2LV5GKLymRwjix03qYIeM';
$config['google.recaptcha.publicKey']  = '6Ldg2wsUAAAAALpMHPiHEtZJ_SGycdDa11Kt_IOh';
$config['google.recaptcha.privateKey'] = '6Ldg2wsUAAAAANCLllmQfCg5jSWUbJD6rSjEmtSL';

/*
 * Template folders for pages
 */
$config['system.template.path'] = '/html';
$config['template.admin']     = $config['system.template.path'] . '/sbadmin2/admin.html';
$config['template.client']    = $config['system.template.path'] . '/sbadmin2/admin.html';
$config['template.staff']     = $config['system.template.path'] . '/sbadmin2/admin.html';
$config['template.student']   = $config['system.template.path'] . '/sbadmin2/admin.html';
$config['template.public']    = $config['system.template.path'] . '/sbadmin2/public.html';

/*
 * This path is where designers can place templates that override the system default templates.
 * Relative Path for renderer custom templates, this will reside in the above user template folders
 * EG: $path = dirname($config['template.admin']) . $config['template.xtpl.path'];
 * @var {templatePath} will be replaced by the path of the current user page template
 */
$config['template.xtpl.path'] = $config->getSitePath() . $config['system.template.path'] . '/app/xtpl';
//$config['template.xtpl.path'] = '{templatePath}/xtpl';
$config['template.xtpl.ext'] = '.xtpl';


/*
 * Change the system timezone
 */
$config['date.timezone'] = 'Australia/Victoria';

/*
 * Enable logging of triggered events
 * Default: false
 */
$config['event.dispatcher.log'] = false;

/*
 * Max size for profile images
 * Default; 1028*1028*2 (2M)
 */
$config['upload.profile.imagesize'] = 1028*1028*2;

/*
 * The session log allows us to add the log to exception emails
 */
$config['log.session'] = $config->getTempPath().'/session.log';

/*
 * if set to true then all required form fields will render the required="required" attribute
 * currently disabled by default as the errors do not play well with tabs, wizards and fields that are hidden
 * it causes the error popup to float to the top of the screen.
 */
//$config['system.form.required.attr.enabled'] = false;

/*
 * Enable exception emails
 */
//$config['system.email.exception'] = array('user@example.com');

/*
 * Send copies of all system emails to these recipients (not error emails)
 */
//$config['mail.bcc'] = array('user1@example.edu.au');



/*  
 * ---- AUTH CONFIG ----
 */

/*
 * The hash function to use for passwords and general hashing
 * Warning if you change this after user account creation
 * users will have to reset/recover their passwords
 */
//$config['hash.function'] = 'md5';

/*
 * Config for the \Tk\Auth\Adapter\DbTable
 */
$config['system.auth.dbtable.tableName']      = 'user';
$config['system.auth.dbtable.usernameColumn'] = 'username';
$config['system.auth.dbtable.passwordColumn'] = 'password';
$config['system.auth.dbtable.saltColumn']     = 'hash';
$config['system.auth.dbtable.activeColumn']   = 'active';



// ------------------------------------------------------------

// Include any overriding config options
include_once(__DIR__ . '/config.php');

// ------------------------------------------------------------

