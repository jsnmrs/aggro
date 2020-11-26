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
    '.docksal',
    '.env*',
    '.git',
    '.github',
    '*.sql',
    '*DS_Store',
    'deploy.php',
    'build',
    'node_modules',
    'public/thumbs',
    'tests',
    'writable/cache',
    'writable/debugbar',
    'writable/logs',
  ],
]);

// Copy dotenv file from github secret to server.
task('deploy:secrets', function () {
  file_put_contents(__DIR__ . '/.env', getenv('DOT_ENV'));
  upload('.env', get('deploy_path'));
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
  'deploy:shared',
  'deploy:vendors',
  'deploy:writable',
  'deploy:symlink',
  'deploy:unlock',
  'cleanup',
]);
