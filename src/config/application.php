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
include_once(__DIR__ . '/routes.php');


/**************************************
 * Default app config values
 **************************************/

/*
 * Template folders for pages
 */
$config['template.admin.path'] = '/html/default';
$config['template.public.path'] = '/html/default';

/*
 * Change the system timezone
 */
//$config['date.timezone'] = 'Australia/Victoria';




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
 * Set the default table names in the DB
 */
\Tk\Plugin\Factory::$DB_TABLE = '_plugin';
\Tk\Util\SqlMigrate::$DB_TABLE = '_migration';
\Tk\Session\Adapter\Database::$DB_TABLE = '_session';
\Ts\Db\Data::$DB_TABLE = '_data';
\App\Factory::$LTI_DB_PREFIX = '_';
\Tk\Db\Map\Mapper::$DB_PREFIX = ''; // Disabled, not used in this app

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
$config['system.auth.adapters'] = array(
    'LDAP' => '\App\Auth\Adapter\UnimelbLdap',
    'DbTable' => '\App\Auth\Adapter\DbTable'
);



