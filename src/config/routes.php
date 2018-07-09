<?php

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
$config = \App\Config::getInstance();
$routes = $config->getRouteCollection();
if (!$routes) return;


// Default Home catchall
$params = array();
$routes->add('home', new \Tk\Routing\Route('/index.html', 'App\Controller\Index::doDefault', $params));
$routes->add('home-base', new \Tk\Routing\Route('/', 'App\Controller\Index::doDefault', $params));
$routes->add('contact', new \Tk\Routing\Route('/contact.html', 'App\Controller\Contact::doDefault', $params));


// Admin Pages
$params = array('role' => \Uni\Db\User::ROLE_ADMIN);
$routes->add('admin-dashboard', new \Tk\Routing\Route('/admin/index.html', 'App\Controller\Admin\Dashboard::doDefault', $params));
$routes->add('admin-dashboard-base', new \Tk\Routing\Route('/admin/', 'App\Controller\Admin\Dashboard::doDefault', $params));


// Client Pages
$params = array('role' => \Uni\Db\User::ROLE_CLIENT);
$routes->add('client-dashboard', new \Tk\Routing\Route('/client/index.html', 'App\Controller\Client\Dashboard::doDefault', $params));
$routes->add('client-dashboard-base', new \Tk\Routing\Route('/client/', 'App\Controller\Client\Dashboard::doDefault', $params));


// Staff Pages
$params = array('role' => \Uni\Db\User::ROLE_STAFF);
$routes->add('staff-dashboard', new \Tk\Routing\Route('/staff/index.html', 'App\Controller\Staff\Dashboard::doDefault', $params));
$routes->add('staff-dashboard-base', new \Tk\Routing\Route('/staff/', 'App\Controller\Staff\Dashboard::doDefault', $params));



// Student Pages
$params = array('role' => \Uni\Db\User::ROLE_STUDENT);
$routes->add('student-dashboard', new \Tk\Routing\Route('/student/index.html', 'App\Controller\Student\Dashboard::doDefault', $params));
$routes->add('student-dashboard-base', new \Tk\Routing\Route('/student/', 'App\Controller\Student\Dashboard::doDefault', $params));








