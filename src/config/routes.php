<?php
/**
 * Created by PhpStorm.
 *
 * @date 16-05-2016
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
/* 
 * NOTE: Be sure to add routes in correct order as the first match will win
 * 
 * Route Structure
 * $route = new Route(
 *     '/archive/{month}',              // path
 *     '\Namespace\Class::method',      // Callable or class::method string
 *     array('month' => 'Jan'),         // Params and defaults to path params... all will be sent to the request object.
 *     array('GET', 'POST', 'HEAD')     // methods
 * );
 */

$config = \Tk\Config::getInstance();

$routes = new \Tk\Routing\RouteCollection();
$config['site.routes'] = $routes;

// Default Home catchall
$routes->add('home', new \Tk\Routing\Route('/index.html', 'App\Controller\Index::doDefault'));
$routes->add('home-base', new \Tk\Routing\Route('/', 'App\Controller\Index::doDefault'));
$routes->add('contact', new \Tk\Routing\Route('/contact.html', 'App\Controller\Contact::doDefault'));

$routes->add('login', new \Tk\Routing\Route('/login.html', 'App\Controller\Login::doDefault'));
$routes->add('staff-login', new \Tk\Routing\Route('/staff/login.html', 'App\Controller\Login::doStaffLogin'));
$routes->add('student-login', new \Tk\Routing\Route('/student/login.html', 'App\Controller\Login::doStudentLogin'));
$routes->add('recover', new \Tk\Routing\Route('/recover.html', 'App\Controller\Recover::doDefault'));

// Admin Pages
$params = array('test' => 'admin');
$routes->add('admin-home', new \Tk\Routing\Route('/admin/index.html', 'App\Controller\Admin\Index::doDefault', $params));
$routes->add('admin-home-base', new \Tk\Routing\Route('/admin/', 'App\Controller\Admin\Index::doDefault', $params));

$routes->add('admin-institution-manager', new \Tk\Routing\Route('/admin/institutionManager.html', 'App\Controller\Admin\Institution\Manager::doDefault', $params));
$routes->add('admin-institution-edit', new \Tk\Routing\Route('/admin/institutionEdit.html', 'App\Controller\Admin\Institution\Edit::doDefault', $params));

$routes->add('admin-course-manager', new \Tk\Routing\Route('/admin/courseManager.html', 'App\Controller\Admin\Course\Manager::doDefault', $params));
$routes->add('admin-course-edit', new \Tk\Routing\Route('/admin/courseEdit.html', 'App\Controller\Admin\Course\Edit::doDefault', $params));

$routes->add('admin-user-manager', new \Tk\Routing\Route('/admin/userManager.html', 'App\Controller\Admin\User\Manager::doDefault', $params));
$routes->add('admin-user-edit', new \Tk\Routing\Route('/admin/userEdit.html', 'App\Controller\Admin\User\Edit::doDefault', $params));
$routes->add('admin-user-profile', new \Tk\Routing\Route('/admin/profile.html', 'App\Controller\Admin\User\Edit::doDefault', $params));




