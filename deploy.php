<?php

/**
 * Aggro deploy script.
 */

namespace Deployer;

require 'recipe/codeigniter.php';
require 'recipe/rsync.php';

set('application', 'aggro');
set('ssh_multiplexing', TRUE);
set('writable_mode', 'chmod');
set('shared_dirs', ['public/thumbs', 'writable/cache', 'writable/logs']);
set('writable_dirs', ['writable/cache', 'writable/logs']);
set('keep_releases', 3);

set('rsync_src', function () {
  return __DIR__;
});

add('rsync', [
  'exclude' => [
    '.browserslistrc',
    '.docksal',
    '.editorconfig',
    '.env*',
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

// Copy crontab settings from repo.
task('deploy:cron', function () {
  run('cd ~/');
  run('crontab ' . get('release_path') . '/.crontab');
  run('crontab -l');
});

host('bmxfeed.com')
  ->hostname('bmxfeed.com')
  ->stage('production')
  ->user('bmxfeed')
  ->set('deploy_path', '/home/bmxfeed/aggro');

after('deploy:failed', 'deploy:unlock');

desc('Deploy the application');
task('deploy', [
  'deploy:info',
  'deploy:prepare',
  'deploy:lock',
  'deploy:release',
  'rsync',
  'deploy:shared',
  'deploy:writable',
  'deploy:secrets',
  'deploy:symlink',
  'deploy:cron',
  'deploy:unlock',
  'cleanup',
]);
