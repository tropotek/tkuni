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
$config = \Tk\Config::getInstance();
$params = array();
$routes = new \Tk\Routing\RouteCollection();
$config['site.routes'] = $routes;


// Default Home catchall
$routes->add('home', new \Tk\Routing\Route('/index.html', 'App\Controller\Index::doDefault', $params));
$routes->add('home-base', new \Tk\Routing\Route('/', 'App\Controller\Index::doDefault', $params));
$routes->add('contact', new \Tk\Routing\Route('/contact.html', 'App\Controller\Contact::doDefault', $params));
$routes->add('about', new \Tk\Routing\Route('/about.html', 'App\Controller\About::doDefault', $params));

$routes->add('login', new \Tk\Routing\Route('/login.html', 'App\Controller\Login::doDefault', $params));
$routes->add('institution-login', new \Tk\Routing\Route('/inst/{instHash}/login.html', 'App\Controller\Login::doInsLogin', $params));
$routes->add('logout', new \Tk\Routing\Route('/logout.html', 'App\Controller\Logout::doDefault', $params));
$routes->add('recover', new \Tk\Routing\Route('/recover.html', 'App\Controller\Recover::doDefault', $params));
$routes->add('register', new \Tk\Routing\Route('/register.html', 'App\Controller\Register::doDefault', $params));

// LTI launch
//$routes->add('lti-launch', new \Tk\Routing\Route('/lti/launch.html', 'Lti\Controller\Launch::doLaunch', $params));
//$routes->add('institution-lti-launch', new \Tk\Routing\Route('/lti/{instHash}/launch.html', 'Lti\Controller\Launch::doInsLaunch', $params));



// Admin Pages
$params = array('role' => \App\Auth\Acl::ROLE_ADMIN);
$routes->add('admin-home', new \Tk\Routing\Route('/admin/index.html', 'App\Controller\Admin\Dashboard::doDefault', $params));
$routes->add('admin-home-base', new \Tk\Routing\Route('/admin/', 'App\Controller\Admin\Dashboard::doDefault', $params));

$routes->add('admin-institution-manager', new \Tk\Routing\Route('/admin/institutionManager.html', 'App\Controller\Admin\Institution\Manager::doDefault', $params));
$routes->add('admin-institution-edit', new \Tk\Routing\Route('/admin/institutionEdit.html', 'App\Controller\Admin\Institution\Edit::doDefault', $params));
$routes->add('admin-institution-plugin-manager', new \Tk\Routing\Route('/admin/{zoneName}/{zoneId}/plugins.html', 'App\Controller\PluginZoneManager::doDefault',
    array('role' => \App\Auth\Acl::ROLE_ADMIN, 'zoneName' => 'institution', 'zoneId' => '0') ));

$routes->add('admin-user-manager', new \Tk\Routing\Route('/admin/userManager.html', 'App\Controller\Ui\User\Manager::doDefault', $params));
$routes->add('admin-user-edit', new \Tk\Routing\Route('/admin/userEdit.html', 'App\Controller\Ui\User\Edit::doDefault', $params));
$routes->add('admin-user-profile', new \Tk\Routing\Route('/admin/profile.html', 'App\Controller\Ui\Profile::doDefault', $params));

$routes->add('admin-settings', new \Tk\Routing\Route('/admin/settings.html', 'App\Controller\Admin\Settings::doDefault', $params));
$routes->add('admin-plugin-manager', new \Tk\Routing\Route('/admin/plugins.html', 'App\Controller\Admin\PluginManager::doDefault', $params));


// Dev pages
$routes->add('dev-events', new \Tk\Routing\Route('/admin/dev/events.html', 'App\Controller\Admin\Dev\SystemEvents::doDefault', $params));


// Staff Pages
$params = array('role' => \App\Auth\Acl::ROLE_STAFF);
$routes->add('staff-home', new \Tk\Routing\Route('/staff/index.html', 'App\Controller\Staff\Dashboard::doDefault', $params));
$routes->add('staff-home-base', new \Tk\Routing\Route('/staff/', 'App\Controller\Staff\Dashboard::doDefault', $params));

$routes->add('staff-course-manager', new \Tk\Routing\Route('/staff/courseManager.html', 'App\Controller\Ui\Course\Manager::doDefault', $params));
$routes->add('staff-course-edit', new \Tk\Routing\Route('/staff/courseEdit.html', 'App\Controller\Ui\Course\Edit::doDefault', $params));
$routes->add('staff-course-enrollment', new \Tk\Routing\Route('/staff/courseEnrollment.html', 'App\Controller\Ui\Course\Enrollment::doDefault', $params));

$routes->add('staff-user-manager', new \Tk\Routing\Route('/staff/userManager.html', 'App\Controller\Ui\User\Manager::doDefault', $params));
$routes->add('staff-user-edit', new \Tk\Routing\Route('/staff/userEdit.html', 'App\Controller\Ui\User\Edit::doDefault', $params));
$routes->add('staff-user-profile', new \Tk\Routing\Route('/staff/profile.html', 'App\Controller\Ui\Profile::doDefault', $params));



// Student Pages
$params = array('role' => \App\Auth\Acl::ROLE_STUDENT);
$routes->add('student-home', new \Tk\Routing\Route('/student/index.html', 'App\Controller\Student\Dashboard::doDefault', $params));
$routes->add('student-home-base', new \Tk\Routing\Route('/student/', 'App\Controller\Student\Dashboard::doDefault', $params));

$routes->add('student-user-profile', new \Tk\Routing\Route('/student/profile.html', 'App\Controller\Ui\Profile::doDefault', $params));



