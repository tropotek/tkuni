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


date_default_timezone_set('Australia/Victoria');


// Template folders for pages
$config['template.admin.path'] = '/html/admin';
$config['template.public.path'] = '/html/purpose';


// -- AUTH CONFIG --

// DbTable
$config['system.auth.dbtable.tableName'] = 'user';
$config['system.auth.dbtable.usernameColumn'] = 'username';
$config['system.auth.dbtable.passwordColumn'] = 'password';
$config['system.auth.dbtable.saltColumn'] = 'hash';
$config['system.auth.dbtable.activeColumn'] = 'active';

// LDAPs
//$config['system.auth.ldap.host']    = 'centaur.unimelb.edu.au';
//$config['system.auth.ldap.tls']    = true;
//$config['system.auth.ldap.port']   = 389;
//$config['system.auth.ldap.baseDn'] = 'ou=people,o=unimelb';
//$config['system.auth.ldap.filter'] = 'uid={username}';

$config['system.auth.adapters'] = array(
    'LDAP' => '\Uni\Auth\LdapAdapter',  
    'DbTable' => '\Tk\Auth\Adapter\DbTable'
    //'LDAP' => '\Tk\Auth\Adapter\Ldap',
    //'Trap' => '\Tk\Auth\Adapter\Trapdoor'
);


// To avoid var dump errors when debug lib not present
// TODO: there could be a better way to handle this in the future 
if (!class_exists('\Tk\Vd')) {
    function vd() {}
    function vdd() {}
}

