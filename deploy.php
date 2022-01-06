<?php
namespace Deployer;

require 'recipe/common.php';
require 'contrib/rsync.php';

// Config
set('repository', 'https://jsnmrs@github.com/jsnmrs/aggro.git');
set('writable_mode', 'chmod');
set('keep_releases', 3);
add('shared_dirs', ['public/thumbs', 'writable/cache', 'writable/logs']);
add('writable_dirs', ['writable/cache', 'writable/logs']);

// Hosts
host('bmxfeed.com')
  ->setHostname('bmxfeed.com')
  ->setRemoteUser('bmxfeed')
  ->setConfigFile('/var/www/.ssh/config')
  ->setDeployPath('/home/bmxfeed/aggro');

// rsync from local.
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
    '.ssh',
    '.stylelintrc',
    'build',
    'composer.json',
    'composer.lock',
    'deploy.php',
    'deploy.sh',
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
    'vendor/bin',
    'writable/cache',
    'writable/debugbar',
    'writable/logs',
  ],
]);

// Copy dotenv file from github secret to server.
task('deploy:secrets', function () {
  upload('.env-production', get('release_path') . '/.env');
});

// Copy crontab settings from repo.
task('deploy:cron', function () {
  run('cd ~/');
  run('crontab ' . get('release_path') . '/.crontab');
  run('crontab -l');
});

desc('Deploy the application');
task('deploy', [
  'deploy:info',
  'deploy:prepare',
  'deploy:release',
  'rsync',
  'deploy:shared',
  'deploy:writable',
  'deploy:secrets',
  'deploy:symlink',
  'deploy:cron',
  'deploy:unlock'
]);

after('deploy:failed', 'deploy:unlock');
