<?php

/**
 * Aggro deploy script.
 */

namespace Deployer;

require 'recipe/codeigniter.php';
require 'recipe/rsync.php';


set('application', 'aggro');
// Speed up deployment.
set('ssh_multiplexing', TRUE);

set('rsync_src', function () {
  // Project is in root.
  return __DIR__;
});

add('rsync', [
  'exclude' => [
    '.browserlistrc',
    '.cron*',
    '.docksal',
    '.editorconfig',
    '.env*',
    '.eslintrc',
    '.git',
    '.github',
    '.gitignore',
    '*.sql',
    '.stylelintrc',
    'build',
    'composer.json',
    'composer.lock',
    'deploy.php',
    '*DS_Store',
    'LICENSE',
    'node_modules',
    'package.json',
    'package-lock.json',
    'phpcs.xml',
    'phpmd.xml',
    'phpunit.xml.dist',
    'postcss.config.js',
    'public/thumbs',
    'README.md',
    'tests',
    'writable/cache',
    'writable/debugbar',
    'writable/logs',
  ],
]);

// Copy dotenv file from github secret to server.
task('deploy:secrets', function () {
  file_put_contents(__DIR__ . '/.env', getenv('DOT_ENV'));
  upload('.env', get('release_path'));
});


host('production.aggro')
  ->hostname('bmxfeed.com')
  ->stage('production')
  ->user('bmxfeed')
  ->set('deploy_path', '/home/bmxfeed/aggro-prod');

after('deploy:failed', 'deploy:unlock');

desc('Deploy the application');
task('deploy', [
  'deploy:info',
  'deploy:prepare',
  'deploy:lock',
  'deploy:release',
  'rsync',
  'deploy:secrets',
  'deploy:symlink',
  'deploy:unlock',
  'cleanup',
]);
