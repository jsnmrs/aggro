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
    ->setDeployPath('/home/bmxfeed/aggro')
    ->set('base_url', 'https://bmxfeed.com');

host('dev.bmxfeed.com')
    ->set('labels', ['stage' => 'dev'])
    ->setHostname('dev.bmxfeed.com')
    ->setRemoteUser('bmxfeed')
    ->setDeployPath('/home/bmxfeed/aggro-dev')
    ->set('base_url', 'https://dev.bmxfeed.com');

if (file_exists('/home/.ssh/config')) {
    host('bmxfeed.com')->setConfigFile('/home/.ssh/config');
}

// rsync from local.
set('rsync_src', static fn () => __DIR__);

add('rsync', [
    'exclude' => [
        '.browserslistrc',
        '.ddev',
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
    $envContent = '';

    if (getenv('DOT_ENV')) {
        $envContent = getenv('DOT_ENV');
    } elseif (file_exists('.env-production')) {
        $envContent = file_get_contents('.env-production');
    }

    // If SENTRY_RELEASE is provided via environment, add/update it
    if ($envContent && getenv('SENTRY_RELEASE')) {
        // Remove any existing SENTRY_RELEASE line
        $envContent = preg_replace('/^SENTRY_RELEASE\s*=.*$/m', '', $envContent);
        $envContent = preg_replace('/^#\s*SENTRY_RELEASE.*$/m', '', $envContent);

        // Add the new SENTRY_RELEASE after SENTRY_ENVIRONMENT
        $envContent = preg_replace(
            '/(SENTRY_ENVIRONMENT\s*=.*$)/m',
            "$1\nSENTRY_RELEASE = '" . getenv('SENTRY_RELEASE') . "'",
            $envContent,
        );
    }

    // Inject deploy metadata
    if ($envContent) {
        $releaseName     = get('release_name');
        $deployTimestamp = date('Y-m-d H:i:s T');

        $envContent = preg_replace('/^DEPLOY_RELEASE\s*=.*$/m', '', $envContent);
        $envContent = preg_replace('/^DEPLOY_TIMESTAMP\s*=.*$/m', '', $envContent);

        $envContent .= "\nDEPLOY_RELEASE='" . $releaseName . "'";
        $envContent .= "\nDEPLOY_TIMESTAMP='" . $deployTimestamp . "'";
    }

    if ($envContent) {
        file_put_contents(__DIR__ . '/.env-production', $envContent);
        upload('.env-production', get('release_or_current_path') . '/.env');
    }
});

// Copy crontab settings from repo (production only).
task('deploy:cron', static function () {
    if (get('labels')['stage'] !== 'prod') {
        writeln('<comment>Skipping crontab (non-production)</comment>');

        return;
    }

    run('cd ~/');
    run('crontab ' . get('release_or_current_path') . '/.crontab');
    run('crontab -l');
});

// Clear PHP opcache after symlink swap.
// Shared hosting (DreamHost) doesn't allow PHP-FPM restarts,
// so we deploy a temporary script, hit it via HTTP, then remove it.
task('deploy:opcache_clear', static function () {
    $releasePath = get('release_or_current_path');
    $baseUrl     = get('base_url');
    $token       = bin2hex(random_bytes(16));
    $scriptPath  = $releasePath . '/public/_opcache_clear_' . $token . '.php';
    $scriptUrl   = $baseUrl . '/_opcache_clear_' . $token . '.php';

    // Upload a one-shot opcache and realpath cache reset script
    $scriptContent = '<?php clearstatcache(true); if (function_exists("opcache_reset")) { opcache_reset(); echo "cleared"; } else { echo "no_opcache"; }';
    run('echo ' . escapeshellarg($scriptContent) . ' > ' . escapeshellarg($scriptPath));

    // Hit the script multiple times to reach different PHP-FPM workers
    for ($i = 1; $i <= 3; $i++) {
        $result = runLocally("curl -s --max-time 10 '{$scriptUrl}'");
        writeln("<info>opcache_reset result ({$i}/3): {$result}</info>");
    }

    // Remove the script
    run('rm -f ' . escapeshellarg($scriptPath));
});

// Verify deployment by checking the live site.
task('deploy:verify', static function () {
    $baseUrl         = get('base_url');
    $deployPath      = get('deploy_path');
    $expectedRelease = get('release_name');
    $maxAttempts     = 3;
    $retryDelaySecs  = 5;

    // Verify release via SSH (filesystem is authoritative, not subject to PHP-FPM caching)
    $currentTarget = run("readlink {$deployPath}/current");
    $actualRelease = basename($currentTarget);
    writeln("Symlink target: <info>{$currentTarget}</info>");

    if ($actualRelease === $expectedRelease) {
        writeln("<info>Release number matches: {$actualRelease}</info>");
    } else {
        warning("Release mismatch: expected {$expectedRelease}, got {$actualRelease}");
    }

    // Verify .env has the correct DEPLOY_RELEASE
    $envRelease = run("grep -oP \"DEPLOY_RELEASE='\\K[^']+\" {$deployPath}/current/.env || echo 'not found'");
    if ($envRelease === $expectedRelease) {
        writeln("<info>.env DEPLOY_RELEASE matches: {$envRelease}</info>");
    } else {
        warning(".env DEPLOY_RELEASE mismatch: expected {$expectedRelease}, got {$envRelease}");
    }

    // HTTP smoke test (just check that the site responds)
    // Pass basic auth credentials for dev deployments
    $curlAuth = '';
    $authUser = run("grep -oP \"BASIC_AUTH_USER=\\\"?\\K[^\\\"]+\" {$deployPath}/current/.env 2>/dev/null || echo ''");
    $authPass = run("grep -oP \"BASIC_AUTH_PASS=\\\"?\\K[^\\\"]+\" {$deployPath}/current/.env 2>/dev/null || echo ''");
    if ($authUser !== '' && $authPass !== '') {
        $curlAuth = '-u ' . escapeshellarg($authUser . ':' . $authPass);
    }

    $homeStatus = runLocally("curl -s -o /dev/null -w '%{http_code}' {$curlAuth} '{$baseUrl}/'");

    if ($homeStatus !== '200') {
        warning("Homepage returned HTTP {$homeStatus} (expected 200)");
    } else {
        writeln('<info>Homepage returned HTTP 200</info>');
    }
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
    'deploy:opcache_clear',
    'deploy:cron',
    'deploy:unlock',
    'deploy:verify',
]);

after('deploy:failed', 'deploy:unlock');
