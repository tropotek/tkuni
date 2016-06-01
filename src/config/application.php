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
$config['template.admin.path'] = '/html/admin';
$config['template.public.path'] = '/html/purpose';


// -- AUTH CONFIG --

// Setup some basic admin page security
$config['system.auth.username'] = 'admin';
$config['system.auth.password'] = 'password';

$config['system.auth.adapters'] = array(
    'Config' => '\Tk\Auth\Adapter\Config',
    'Trap' => '\Tk\Auth\Adapter\Trapdoor'
    //'DbTable' => '\Tk\Auth\Adapter\DbTable',
    //'LDAP' => '\Tk\Auth\Adapter\Ldap'
);





// To avoid var dump errors when debug lib not present
// TODO: there could be a better way to handle this in the future 
if (!class_exists('\Tk\Vd')) {
    function vd() {}
    function vdd() {}
}