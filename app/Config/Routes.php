<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Front');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override('App\Controllers\Front::getError404');
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Front::getIndex');
$routes->get('about', 'Front::getAbout');
$routes->get('featured', 'Front::getIndex');
$routes->get('opml', 'Feed::getOpml');
$routes->get('rss', 'Feed::getVideofeed');
$routes->get('sites', 'Front::getSites');
$routes->get('sites/(:segment)', 'Front::getSites/$1');
$routes->get('stream', 'Front::getStream');
$routes->get('submit', 'Front::getSubmit');
$routes->get('video', 'Front::getVideo');
$routes->get('video/(:segment)', 'Front::getVideo/$1');
$routes->add('aggro', 'Aggro::getIndex');
$routes->add('aggro/log', 'Aggro::getLog');
$routes->add('aggro/log-error', 'Aggro::getLogError');
$routes->add('aggro/news', 'Aggro::getNews');
$routes->add('aggro/twitter', 'Aggro::postTwitter');
$routes->add('aggro/vimeo', 'Aggro::getVimeo');
$routes->add('aggro/youtube', 'Aggro::getYoutube');
$routes->cli('aggro/log-clean', 'Aggro::getLogClean');
$routes->cli('aggro/log-error-clean', 'Aggro::getLogErrorClean');
$routes->cli('aggro/news-cache', 'Aggro::getNewsCache');
$routes->cli('aggro/news-clean', 'Aggro::getNewsClean');
$routes->cli('aggro/sweep', 'Aggro::getSweep');


/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
