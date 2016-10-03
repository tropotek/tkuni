<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
$config = \Tk\Config::getInstance();

/**
 * Config the session using PHP option names prepended with 'session.'
 * @see http://php.net/session.configuration
 */
include_once(__DIR__ . '/session.php');
include_once(__DIR__ . '/routes.php');

// Template folders for pages
$config['template.admin.path'] = '/html/default';
$config['template.public.path'] = '/html/default';

$config['system.timezone'] = 'Australia/Victoria';

// TODO: implement this into the base config....
$config['system.https'] = true;

// -- AUTH CONFIG --

// The hash function to use for passwords and general hashing
// Warning if you change this after user account creation
// users will have to reset/recover their passwords
$config['hash.function'] = 'md5';


// Do not change after installation
\Tk\Plugin\Factory::$DB_TABLE = '_plugin';
\Tk\Util\SqlMigrate::$DB_TABLE = '_migration';
\Tk\Session\Adapter\Database::$DB_TABLE = '_session';
\Ts\Db\Data::$DB_TABLE = '_data';
\App\Factory::$LTI_DB_PREFIX = '_';
\Tk\Db\Map\Mapper::$DB_PREFIX = ''; // Disabled, not used in this app


// DbTable
$config['system.auth.dbtable.tableName'] = 'user';
$config['system.auth.dbtable.usernameColumn'] = 'username';
$config['system.auth.dbtable.passwordColumn'] = 'password';
$config['system.auth.dbtable.saltColumn'] = 'hash';
$config['system.auth.dbtable.activeColumn'] = 'active';


$config['system.auth.adapters'] = array(
    'LDAP' => '\App\Auth\Adapter\UnimelbLdap',
    'DbTable' => '\App\Auth\Adapter\DbTable'
);



