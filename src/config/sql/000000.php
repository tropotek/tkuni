<?php
/**
 * Created by PhpStorm.
 * User: mifsudm
 * Date: 27/09/16
 * Time: 7:07 AM
 */

$config = \Tk\Config::getInstance();
$data = \Tk\Db\Data::create();
$data->set('site.title', 'tk2uni');
$data->set('site.email', 'fvas-elearning@unimelb.edu.au');
//$data->set('site.client.registration', 'site.client.registration');
//$data->set('site.client.activation', 'site.client.activation');

$data->set('site.meta.keywords', '');
$data->set('site.meta.description', '');
$data->set('site.global.js', '');
$data->set('site.global.css', '');

$data->save();

// TODO: Any other install procedures.




