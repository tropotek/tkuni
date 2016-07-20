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

$params = array();
$routes = new \Tk\Routing\RouteCollection();
$config['site.routes'] = $routes;


// Default Home catchall
$routes->add('home', new \Tk\Routing\Route('/index.html', 'App\Controller\Index::doDefault', $params));
$routes->add('home-base', new \Tk\Routing\Route('/', 'App\Controller\Index::doDefault', $params));
$routes->add('contact', new \Tk\Routing\Route('/contact.html', 'App\Controller\Contact::doDefault', $params));

$routes->add('login', new \Tk\Routing\Route('/login.html', 'App\Controller\Login::doDefault', $params));
$routes->add('logout', new \Tk\Routing\Route('/logout.html', 'App\Controller\Logout::doDefault', $params));
$routes->add('recover', new \Tk\Routing\Route('/recover.html', 'App\Controller\Recover::doDefault', $params));


// Admin Pages
$routes->add('admin-home', new \Tk\Routing\Route('/admin/index.html', 'App\Controller\Admin\Index::doDefault', $params));
$routes->add('admin-home-base', new \Tk\Routing\Route('/admin/', 'App\Controller\Admin\Index::doDefault', $params));

$routes->add('admin-institution-manager', new \Tk\Routing\Route('/admin/institutionManager.html', 'App\Controller\Admin\Institution\Manager::doDefault', $params));
$routes->add('admin-institution-edit', new \Tk\Routing\Route('/admin/institutionEdit.html', 'App\Controller\Admin\Institution\Edit::doDefault', $params));

$routes->add('admin-course-manager', new \Tk\Routing\Route('/admin/courseManager.html', 'App\Controller\Admin\Course\Manager::doDefault', $params));
$routes->add('admin-course-edit', new \Tk\Routing\Route('/admin/courseEdit.html', 'App\Controller\Admin\Course\Edit::doDefault', $params));

$routes->add('admin-user-manager', new \Tk\Routing\Route('/admin/userManager.html', 'App\Controller\Admin\User\Manager::doDefault', $params));
$routes->add('admin-user-edit', new \Tk\Routing\Route('/admin/userEdit.html', 'App\Controller\Admin\User\Edit::doDefault', $params));
$routes->add('admin-user-profile', new \Tk\Routing\Route('/admin/profile.html', 'App\Controller\Admin\User\Edit::doDefault', $params));


// Client Pages
$routes->add('client-home', new \Tk\Routing\Route('/client/index.html', 'App\Controller\Client\Index::doDefault', $params));
$routes->add('client-home-base', new \Tk\Routing\Route('/client/', 'App\Controller\Client\Index::doDefault', $params));



// Staff Pages
$routes->add('staff-home', new \Tk\Routing\Route('/staff/index.html', 'App\Controller\Staff\Index::doDefault', $params));
$routes->add('staff-home-base', new \Tk\Routing\Route('/staff/', 'App\Controller\Staff\Index::doDefault', $params));



// Student Pages
$routes->add('student-home', new \Tk\Routing\Route('/student/index.html', 'App\Controller\Student\Index::doDefault', $params));
$routes->add('student-home-base', new \Tk\Routing\Route('/student/', 'App\Controller\Student\Index::doDefault', $params));







