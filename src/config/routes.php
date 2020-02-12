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
$routes->add('home', new \Tk\Routing\Route('/index.html', 'App\Controller\Index::doDefault'));
$routes->add('home-base', new \Tk\Routing\Route('/', 'App\Controller\Index::doDefault'));

$routes->add('login', new \Tk\Routing\Route('/login.html', 'App\Controller\Login::doDefault'));
$routes->add('institution-login', new \Tk\Routing\Route('/inst/{instHash}/login.html', 'App\Controller\Login::doInsLogin'));
$routes->add('recover', new \Tk\Routing\Route('/recover.html', 'App\Controller\Recover::doDefault'));


// Admin Pages
$routes->add('admin-dashboard', new \Tk\Routing\Route('/admin/index.html', 'App\Controller\Admin\Dashboard::doDefault'));
$routes->add('admin-dashboard-base', new \Tk\Routing\Route('/admin/', 'App\Controller\Admin\Dashboard::doDefault'));
$routes->add('admin-settings', new \Tk\Routing\Route('/admin/settings.html', 'App\Controller\Admin\Settings::doDefault'));


// Client Pages
$routes->add('client-dashboard', new \Tk\Routing\Route('/client/index.html', 'App\Controller\Client\Dashboard::doDefault'));
$routes->add('client-dashboard-base', new \Tk\Routing\Route('/client/', 'App\Controller\Client\Dashboard::doDefault'));


// Staff Pages
$routes->add('staff-dashboard', new \Tk\Routing\Route('/staff/index.html', 'App\Controller\Staff\Dashboard::doDefault'));
$routes->add('staff-dashboard-base', new \Tk\Routing\Route('/staff/', 'App\Controller\Staff\Dashboard::doDefault'));
$routes->add('staff-subject-dashboard', new \Tk\Routing\Route('/staff/{subjectCode}/index.html', 'App\Controller\Staff\SubjectDashboard::doDefault'));


// Student Pages
$routes->add('student-dashboard', new \Tk\Routing\Route('/student/index.html', 'App\Controller\Student\Dashboard::doDefault'));
$routes->add('student-dashboard-base', new \Tk\Routing\Route('/student/', 'App\Controller\Student\Dashboard::doDefault'));
$routes->add('student-subject-dashboard', new \Tk\Routing\Route('/student/{subjectCode}/index.html', 'App\Controller\Student\SubjectDashboard::doDefault'));



// Dev
$routes->add('admin-dev-forms', new \Tk\Routing\Route('/admin/dev/forms.html', 'App\Controller\Dev\Forms::doDefault'));


