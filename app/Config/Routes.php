<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Front::getIndex');
$routes->add('/', 'Front::getIndex');
$routes->add('about', 'Front::getAbout');
$routes->add('featured', 'Front::getIndex');
$routes->add('feed', 'Feed::getNewsfeed');
$routes->add('opml', 'Feed::getOpml');
$routes->add('rss', 'Feed::getVideofeed');
$routes->add('sites', 'Front::getSites');
$routes->add('sites/(:segment)', 'Front::getSites/$1');
$routes->add('stream', 'Front::getStream');
$routes->add('submit', 'Front::getSubmit');
$routes->add('video', 'Front::getVideo');
$routes->add('video/(:any)', 'Front::getVideo/$1');
$routes->add('watch', 'Front::getWatch');
$routes->add('aggro', 'Aggro::getIndex');
$routes->add('aggro/watch', 'Aggro::changeWatch');
$routes->add('aggro/info', 'Aggro::getInfo');
$routes->add('aggro/log', 'Aggro::getLog');
$routes->add('aggro/log-error', 'Aggro::getLogError');
$routes->add('aggro/news', 'Aggro::getNews');
$routes->add('aggro/vimeo', 'Aggro::getVimeo');
$routes->add('aggro/vimeo/(:segment)', 'Aggro::getVimeo/$1');
$routes->add('aggro/youtube', 'Aggro::getYoutube');
$routes->add('aggro/youtube/(:segment)', 'Aggro::getYoutube/$1');
$routes->add('aggro/duration', 'Aggro::getYouTubeDuration');
$routes->add('aggro/log-clean', 'Aggro::getLogClean');
$routes->add('aggro/log-error-clean', 'Aggro::getLogErrorClean');
$routes->add('aggro/news-cache', 'Aggro::getNewsCache');
$routes->add('aggro/news-clean', 'Aggro::getNewsClean');
$routes->add('aggro/sweep', 'Aggro::getSweep');
