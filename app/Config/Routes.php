<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Front::getIndex');
$routes->get('about', 'Front::getAbout');
$routes->get('robots.txt', 'Front::robots');
$routes->get('sitemap.xml', 'Front::sitemap');
$routes->get('featured', 'Front::getIndex');
$routes->get('feed', 'Feed::getNewsfeed');
$routes->get('opml', 'Feed::getOpml');
$routes->get('rss', 'Feed::getVideofeed');
$routes->get('sites', 'Front::getSites');
$routes->get('sites/(:segment)', 'Front::getSites/$1');
$routes->get('stream', 'Front::getStream');
$routes->get('video', 'Front::getVideo');
$routes->get('video/(:any)', 'Front::getVideo/$1');
$routes->get('aggro', 'Aggro::getIndex');
$routes->get('aggro/info', 'Aggro::getInfo');
$routes->get('aggro/log', 'Aggro::getLog');
$routes->get('aggro/log-error', 'Aggro::getLogError');
$routes->get('aggro/news', 'Aggro::getNews');
$routes->get('aggro/vimeo', 'Aggro::getVimeo');
$routes->get('aggro/vimeo/(:segment)', 'Aggro::getVimeo/$1');
$routes->get('aggro/youtube', 'Aggro::getYoutube');
$routes->get('aggro/youtube/(:segment)', 'Aggro::getYoutube/$1');
$routes->get('aggro/duration', 'Aggro::getYouTubeDuration');
$routes->post('aggro/log-clean', 'Aggro::getLogClean');
$routes->post('aggro/log-error-clean', 'Aggro::getLogErrorClean');
$routes->post('aggro/news-cache', 'Aggro::getNewsCache');
$routes->post('aggro/news-clean', 'Aggro::getNewsClean');
$routes->post('aggro/sweep', 'Aggro::getSweep');
$routes->cli('aggro/log-clean', 'Aggro::getLogClean');
$routes->cli('aggro/log-error-clean', 'Aggro::getLogErrorClean');
$routes->cli('aggro/news-cache', 'Aggro::getNewsCache');
$routes->cli('aggro/news-clean', 'Aggro::getNewsClean');
$routes->cli('aggro/sweep', 'Aggro::getSweep');

// Set 404 override
$routes->set404Override('App\Controllers\Front::getError404');
