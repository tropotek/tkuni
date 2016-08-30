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

$config['site.title'] = 'Tk2Uni Site';
$config['site.email'] = 'tkwiki@example.com';

//$config['site.meta.keywords'] = '';
//$config['site.meta.description'] = '';
//$config['site.global.js'] = '';
//$config['site.global.css'] = '';


// Template folders for pages
$config['template.admin.path'] = '/html/admin';
$config['template.public.path'] = '/html/admin';

$config['system.timezone'] = 'Australia/Victoria';

// TODO: implement this into the base config....
$config['system.https'] = true;

// -- AUTH CONFIG --

// The hash function to use for passwords and general hashing
// Warning if you change this after user account creation
// users will have to reset/recover their passwords
$config['hash.function'] = 'md5';

//$config['site.client.registration'] = false;
//$config['site.client.activation'] = false;


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


// To avoid var dump errors when debug lib not present
// TODO: there could be a better way to handle this in the future 
if (!class_exists('\Tk\Vd')) {
    function vd() {}
    function vdd() {}
}

