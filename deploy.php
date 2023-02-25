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
    ->set('labels', ['stage' => 'prod'])
    ->setHostname('bmxfeed.com')
    ->setRemoteUser('bmxfeed')
    ->setDeployPath('/home/bmxfeed/aggro');

host('dev.bmxfeed.com')
    ->set('labels', ['stage' => 'dev'])
    ->setHostname('dev.bmxfeed.com')
    ->setRemoteUser('bmxfeed')
    ->setDeployPath('/home/bmxfeed/aggro-dev');

if (file_exists('/var/www/.ssh/config')) {
    host('bmxfeed.com')->setConfigFile('/var/www/.ssh/config');
}

// rsync from local.
set('rsync_src', static fn () => __DIR__);

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

// Move .env file (from disk or action)
task('deploy:secrets', static function () {
    if (getenv('DOT_ENV')) {
        file_put_contents(__DIR__ . '/.env-production', getenv('DOT_ENV'));
    }
    if (file_exists('.env-production')) {
        upload('.env-production', get('release_or_current_path') . '/.env');
    }
});

// Copy crontab settings from repo.
task('deploy:cron', static function () {
    run('cd ~/');
    run('crontab ' . get('release_or_current_path') . '/.crontab');
    run('crontab -l');
});

desc('Deploy the application');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'rsync',
    'deploy:shared',
    'deploy:writable',
    'deploy:secrets',
    'deploy:symlink',
    'deploy:cron',
    'deploy:unlock',
]);

after('deploy:failed', 'deploy:unlock');
