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
$routes = new \Tk\Routing\RouteCollection();
$config['site.routes'] = $routes;


// Default Home catchall
$params = array();
$routes->add('home', new \Tk\Routing\Route('/index.html', 'App\Controller\Index::doDefault', $params));
$routes->add('home-base', new \Tk\Routing\Route('/', 'App\Controller\Index::doDefault', $params));
$routes->add('contact', new \Tk\Routing\Route('/contact.html', 'App\Controller\Contact::doDefault', $params));
$routes->add('about', new \Tk\Routing\Route('/about.html', 'App\Controller\About::doDefault', $params));

$routes->add('login', new \Tk\Routing\Route('/login.html', 'App\Controller\Login::doDefault', $params));
$routes->add('institution-login', new \Tk\Routing\Route('/inst/{instHash}/login.html', 'App\Controller\Login::doInsLogin', $params));
$routes->add('logout', new \Tk\Routing\Route('/logout.html', 'App\Controller\Logout::doDefault', $params));
$routes->add('recover', new \Tk\Routing\Route('/recover.html', 'App\Controller\Recover::doDefault', $params));
$routes->add('register', new \Tk\Routing\Route('/register.html', 'App\Controller\Register::doDefault', $params));


// Admin Pages
$params = array('role' => \App\Db\User::ROLE_ADMIN);
$routes->add('admin-dashboard', new \Tk\Routing\Route('/admin/index.html', 'App\Controller\Admin\Dashboard::doDefault', $params));
$routes->add('admin-dashboard-base', new \Tk\Routing\Route('/admin/', 'App\Controller\Admin\Dashboard::doDefault', $params));
$routes->add('dev-events', new \Tk\Routing\Route('/admin/dev/events.html', 'App\Controller\Admin\Dev\SystemEvents::doDefault', $params));

$routes->add('admin-institution-manager', new \Tk\Routing\Route('/admin/institutionManager.html', 'App\Controller\Institution\Manager::doDefault', $params));
$routes->add('admin-institution-edit', new \Tk\Routing\Route('/admin/institutionEdit.html', 'App\Controller\Institution\Edit::doDefault', $params));
$routes->add('admin-institution-plugin-manager', new \Tk\Routing\Route('/admin/{zoneName}/{zoneId}/plugins.html', 'App\Controller\PluginZoneManager::doDefault',
    array('role' => \App\Db\User::ROLE_ADMIN, 'zoneName' => 'institution', 'zoneId' => '0') ));

$routes->add('admin-user-manager', new \Tk\Routing\Route('/admin/userManager.html', 'App\Controller\User\Manager::doDefault', $params));
$routes->add('admin-user-edit', new \Tk\Routing\Route('/admin/userEdit.html', 'App\Controller\User\Edit::doDefault', $params));
$routes->add('admin-user-profile', new \Tk\Routing\Route('/admin/profile.html', 'App\Controller\User\Profile::doDefault', $params));

$routes->add('admin-settings', new \Tk\Routing\Route('/admin/settings.html', 'App\Controller\Admin\Settings::doDefault', $params));
$routes->add('admin-plugin-manager', new \Tk\Routing\Route('/admin/plugins.html', 'App\Controller\Admin\PluginManager::doDefault', $params));


// Client Pages
$params = array('role' => \App\Db\User::ROLE_CLIENT);
$routes->add('client-dashboard', new \Tk\Routing\Route('/client/index.html', 'App\Controller\Client\Dashboard::doDefault', $params));
$routes->add('client-dashboard-base', new \Tk\Routing\Route('/client/', 'App\Controller\Client\Dashboard::doDefault', $params));

$routes->add('client-user-profile', new \Tk\Routing\Route('/client/profile.html', 'App\Controller\User\Profile::doDefault', $params));
$routes->add('client-staff-manager', new \Tk\Routing\Route('/client/staffManager.html', 'App\Controller\User\StaffManager::doDefault', $params));
$routes->add('client-staff-edit', new \Tk\Routing\Route('/client/staffEdit.html', 'App\Controller\User\StaffEdit::doDefault', $params));
$routes->add('client-student-manager', new \Tk\Routing\Route('/client/studentManager.html', 'App\Controller\User\StudentManager::doDefault', $params));
$routes->add('client-student-edit', new \Tk\Routing\Route('/client/studentEdit.html', 'App\Controller\User\StudentEdit::doDefault', $params));

$routes->add('client-institution-edit', new \Tk\Routing\Route('/client/institutionEdit.html', 'App\Controller\Institution\Edit::doDefault', $params));
$routes->add('client-institution-plugin-manager', new \Tk\Routing\Route('/client/{zoneName}/{zoneId}/plugins.html', 'App\Controller\PluginZoneManager::doDefault',
    array('role' => \App\Db\User::ROLE_CLIENT, 'zoneName' => 'institution', 'zoneId' => '0') ));

$routes->add('client-course-manager', new \Tk\Routing\Route('/client/courseManager.html', 'App\Controller\Course\Manager::doDefault', $params));
$routes->add('client-course-edit', new \Tk\Routing\Route('/client/courseEdit.html', 'App\Controller\Course\Edit::doDefault', $params));
$routes->add('client-course-enrollment', new \Tk\Routing\Route('/client/courseEnrollment.html', 'App\Controller\Course\EnrollmentManager::doDefault', $params));




// Staff Pages
$params = array('role' => \App\Db\User::ROLE_STAFF);
$routes->add('staff-dashboard', new \Tk\Routing\Route('/staff/index.html', 'App\Controller\Staff\Dashboard::doDefault', $params));
$routes->add('staff-dashboard-base', new \Tk\Routing\Route('/staff/', 'App\Controller\Staff\Dashboard::doDefault', $params));

$routes->add('staff-course-manager', new \Tk\Routing\Route('/staff/courseManager.html', 'App\Controller\Course\Manager::doDefault', $params));
$routes->add('staff-course-edit', new \Tk\Routing\Route('/staff/courseEdit.html', 'App\Controller\Course\Edit::doDefault', $params));
$routes->add('staff-course-enrollment', new \Tk\Routing\Route('/staff/courseEnrollment.html', 'App\Controller\Course\EnrollmentManager::doDefault', $params));


$routes->add('staff-student-manager', new \Tk\Routing\Route('/staff/studentManager.html', 'App\Controller\User\StudentManager::doDefault', $params));
$routes->add('staff-student-edit', new \Tk\Routing\Route('/staff/studentEdit.html', 'App\Controller\User\StudentEdit::doDefault', $params));
$routes->add('staff-user-profile', new \Tk\Routing\Route('/staff/profile.html', 'App\Controller\User\Profile::doDefault', $params));


//$routes->add('staff-user-manager', new \Tk\Routing\Route('/staff/userManager.html', 'App\Controller\User\Manager::doDefault', $params));
//$routes->add('staff-user-edit', new \Tk\Routing\Route('/staff/userEdit.html', 'App\Controller\User\Edit::doDefault', $params));
//$routes->add('staff-user-profile', new \Tk\Routing\Route('/staff/profile.html', 'App\Controller\User\Profile::doDefault', $params));



// Student Pages
$params = array('role' => \App\Db\User::ROLE_STUDENT);
$routes->add('student-dashboard', new \Tk\Routing\Route('/student/index.html', 'App\Controller\Student\Dashboard::doDefault', $params));
$routes->add('student-dashboard-base', new \Tk\Routing\Route('/student/', 'App\Controller\Student\Dashboard::doDefault', $params));

$routes->add('student-user-profile', new \Tk\Routing\Route('/student/profile.html', 'App\Controller\User\Profile::doDefault', $params));


// Ajax Urls
$params = array('role' => array(\App\Db\User::ROLE_ADMIN, \App\Db\User::ROLE_CLIENT, \App\Db\User::ROLE_STAFF, \App\Db\User::ROLE_STUDENT));
$routes->add('ajax-user-findFiltered', new \Tk\Routing\Route('/ajax/user/findFiltered.html', 'App\Ajax\User::doFindFiltered', $params));
$routes->add('ajax-course-findFiltered', new \Tk\Routing\Route('/ajax/course/findFiltered.html', 'App\Ajax\Course::doFindFiltered', $params));



