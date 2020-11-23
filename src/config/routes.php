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

use Tk\Routing\Route;

$config = \App\Config::getInstance();
$routes = $config->getRouteCollection();
if (!$routes) return;


// Default Home catchall
$routes->add('home', new Route('/index.html', 'App\Controller\Index::doDefault'));
$routes->add('home-base', new Route('/', 'App\Controller\Index::doDefault'));

$routes->add('login', Route::create('/login.html', 'App\Controller\Login::doInsLogin'));
$routes->add('institution-login', Route::create('/inst/{instHash}/login.html', 'App\Controller\Login::doInsLogin'));
$routes->add('admin-login', Route::create('/xlogin.html', 'App\Controller\Login::doDefault'));

$routes->add('recover', new Route('/recover.html', 'App\Controller\Recover::doDefault'));
$routes->add('install', Route::create('/install.html', 'App\Controller\Install::doDefault'));

// Admin Pages
$routes->add('admin-dashboard', new Route('/admin/index.html', 'App\Controller\Admin\Dashboard::doDefault'));
$routes->add('admin-dashboard-base', new Route('/admin/', 'App\Controller\Admin\Dashboard::doDefault'));
$routes->add('admin-settings', new Route('/admin/settings.html', 'App\Controller\Admin\Settings::doDefault'));


// Client Pages
$routes->add('client-dashboard', new Route('/client/index.html', 'App\Controller\Client\Dashboard::doDefault'));
$routes->add('client-dashboard-base', new Route('/client/', 'App\Controller\Client\Dashboard::doDefault'));

// Mentor Pages
$routes->add('mentor-dashboard', new Route('/staff/mentor/index.html', 'App\Controller\Mentor\Dashboard::doDefault'));
$routes->add('mentor-dashboard-base', new Route('/staff/mentor/', 'App\Controller\Mentor\Dashboard::doDefault'));
$routes->add('mentor-student-view', new Route('/staff/mentor/studentView.html', 'App\Controller\Mentor\StudentView::doDefault'));

// Staff Pages
$routes->add('staff-dashboard', new Route('/staff/index.html', 'App\Controller\Staff\Dashboard::doDefault'));
$routes->add('staff-dashboard-base', new Route('/staff/', 'App\Controller\Staff\Dashboard::doDefault'));
$routes->add('staff-subject-dashboard', new Route('/staff/{subjectCode}/index.html', 'App\Controller\Staff\SubjectDashboard::doDefault'));



// Student Pages
$routes->add('student-dashboard', new Route('/student/index.html', 'App\Controller\Student\Dashboard::doDefault'));
$routes->add('student-dashboard-base', new Route('/student/', 'App\Controller\Student\Dashboard::doDefault'));
$routes->add('student-subject-dashboard', new Route('/student/{subjectCode}/index.html', 'App\Controller\Student\SubjectDashboard::doDefault'));



// Dev
$routes->add('admin-dev-forms', new Route('/admin/dev/forms.html', 'App\Controller\Admin\Dev\Forms::doDefault'));


