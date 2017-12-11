<?php
/*
 * Application default config values
 * This file should not need to be edited
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
$config = \Tk\Config::getInstance();

include_once(__DIR__ . '/session.php');


/**************************************
 * Default app config values
 **************************************/

$config['site.title'] = 'New UNI Site';
$config['site.email'] = 'fvas-elearning@unimelb.edu.au';

$config['google.map.apikey'] = 'AIzaSyCSGICa1zdV6i2LV5GKLymRwjix03qYIeM';
$config['google.recaptcha.publicKey'] = '6Ldg2wsUAAAAALpMHPiHEtZJ_SGycdDa11Kt_IOh';
$config['google.recaptcha.privateKey'] = '6Ldg2wsUAAAAANCLllmQfCg5jSWUbJD6rSjEmtSL';

/*
 * Template folders for pages
 */
$config['system.template.path'] = '/html';
$config['template.admin'] = $config['system.template.path'].'/default/admin.xtpl';
$config['template.staff'] = $config['system.template.path'].'/default/staff.xtpl';
$config['template.student'] = $config['system.template.path'].'/default/student.xtpl';
$config['template.public'] = $config['system.template.path'].'/default/public.xtpl';

// Path for renderer custom templates
$config['template.xtpl.path'] = $config['template.admin.path'] . '/xtpl';

/*
 * Change the system timezone
 */
$config['date.timezone'] = 'Australia/Victoria';

/*
 * TODO: implement this into the base config....
 */
//$config['system.https'] = true;


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
$config['system.auth.dbtable.tableName'] = 'user';
$config['system.auth.dbtable.usernameColumn'] = 'username';
$config['system.auth.dbtable.passwordColumn'] = 'password';
$config['system.auth.dbtable.saltColumn'] = 'hash';
$config['system.auth.dbtable.activeColumn'] = 'active';

/*
 * Auth adapters to use in logging into the site
 */
//$config['system.auth.adapters'] = array(
//    'DbTable' => '\App\Auth\Adapter\DbTable'
//);



/*
 * Set this to true to allow extended email addresses in the format of "User Name <username@domain.com>"
 */
// \Tk\Mail\Message::$ENABLE_EXTENDED_ADDRESS = false;




// ------------------------------------------------------------

// Include any overriding config options
include_once(__DIR__ . '/config.php');

// ------------------------------------------------------------

