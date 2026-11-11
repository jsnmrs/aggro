<?php

namespace Tests\Unit;

use App\Controllers\Aggro;
use CodeIgniter\Test\ControllerTestTrait;
use Config\Database;
use Tests\Support\DatabaseTestCase;
use Tests\Support\TemporaryStorageTrait;

/**
 * @internal
 */
final class AggroControllerTest extends DatabaseTestCase
{
    use ControllerTestTrait;
    use TemporaryStorageTrait;

    protected Aggro $aggroController;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the controller first to see if it works with the test database
        $this->aggroController = new Aggro();
    }

    public function testGetIndexReturnsOutput(): void
    {
        ob_start();
        $this->aggroController->getIndex();
        $output = ob_get_clean();

        $this->assertStringContainsString('running cron all day.', $output);
        $this->assertStringContainsString('color:#005600', $output);
    }

    public function testGetInfoReturnsVersionInfo(): void
    {
        $result = $this->controller(Aggro::class)
            ->execute('getInfo');

        $response = $result->response();

        $this->assertTrue($response->hasHeader('Cache-Control'));
        $this->assertTrue($response->hasHeader('Pragma'));
        $this->assertTrue($response->hasHeader('Expires'));
    }

    public function testGetLogExecutesInCliMode(): void
    {
        // In CLI mode gate_check() returns true, so getLog should succeed
        $result = $this->controller(Aggro::class)
            ->execute('getLog');

        $this->assertTrue($result->isOK());
    }

    public function testGetNewsExecutesInCliMode(): void
    {
        // In CLI mode gate_check() returns true, so getNews should succeed
        $result = $this->controller(Aggro::class)
            ->execute('getNews');

        $this->assertTrue($result->isOK());
    }

    public function testGetNewsReturnsConfirmation(): void
    {
        // In CLI mode gate_check() returns true
        $result = $this->controller(Aggro::class)
            ->execute('getNews');

        $body = $result->response()->getBody();
        $this->assertStringContainsString('Featured page built.', $body);
    }

    public function testGetNewsCacheClearsTheFeedCache(): void
    {
        $this->withFeedCacheDirectory(function (string $directory): void {
            file_put_contents($directory . 'feed.spc', 'cached');

            // In CLI mode gate_check() returns true
            $result = $this->controller(Aggro::class)
                ->execute('getNewsCache');

            $this->assertSame(200, $result->response()->getStatusCode());
            $this->assertSame('Feed caches cleared.', $result->response()->getBody());
            $this->assertFileDoesNotExist($directory . 'feed.spc');
        });
    }

    public function testGetNewsCleanReturnsExpectedMessage(): void
    {
        // In CLI mode gate_check() returns true
        $result = $this->controller(Aggro::class)
            ->execute('getNewsClean');

        $body = $result->response()->getBody();
        $this->assertStringContainsString('Featured news stories cleared.', $body);
    }

    public function testGetSweepChecksAndCleansThumbnails(): void
    {
        $this->withThumbnailDirectory(function (string $directory): void {
            $old = $directory . 'old.webp';
            file_put_contents($old, 'old');
            touch($old, time() - config('Storage')->getCleanupAgeSeconds() - 60);

            // In CLI mode gate_check() returns true
            $result = $this->controller(Aggro::class)
                ->execute('getSweep');

            $this->assertSame(200, $result->response()->getStatusCode());
            $body = (string) $result->response()->getBody();
            $this->assertStringContainsString('Thumbnails checked.', $body);
            $this->assertStringContainsString('Thumbnails cleaned up.', $body);
            $this->assertFileDoesNotExist($old);
        });
    }

    public function testGetPlaysReportsTheRefresh(): void
    {
        // In CLI mode gate_check() returns true
        $result = $this->controller(Aggro::class)
            ->execute('getPlays');

        $this->assertSame(200, $result->response()->getStatusCode());
        $this->assertStringContainsString('Play counts refreshed.', (string) $result->response()->getBody());
    }

    public function testGetVimeoWithoutStaleChannelsFetchesNothing(): void
    {
        // In CLI mode gate_check() returns true, null videoID fetches channels
        $result = $this->controller(Aggro::class)
            ->execute('getVimeo');

        // No channel is due, so nothing is fetched and nothing is reported
        $this->assertSame(200, $result->response()->getStatusCode());
        $this->assertSame('', (string) $result->response()->getBody());
    }

    public function testGetVimeoWithInvalidVideoId(): void
    {
        $_GET['g'] = 'testkey';

        // Test with invalid video ID format - should return 404
        $result = $this->controller(Aggro::class)
            ->execute('getVimeo', 'invalid123');

        $this->assertSame(404, $result->response()->getStatusCode());
    }

    public function testGetYoutubeWithoutStaleChannelsFetchesNothing(): void
    {
        // In CLI mode gate_check() returns true, null videoID fetches channels
        $result = $this->controller(Aggro::class)
            ->execute('getYoutube');

        // No channel is due, so nothing is fetched and nothing is reported
        $this->assertSame(200, $result->response()->getStatusCode());
        $this->assertSame('', (string) $result->response()->getBody());
    }

    public function testGetYoutubeWithInvalidVideoId(): void
    {
        $_GET['g'] = 'testkey';

        // Test with invalid video ID format - should return 404
        $result = $this->controller(Aggro::class)
            ->execute('getYoutube', 'invalid');

        $this->assertSame(404, $result->response()->getStatusCode());
    }

    public function testGetYoutubeDoesNotReportAddedWhenSourceLookupFails(): void
    {
        // Network access is blocked in tests, so the source lookup for a
        // well-formed video ID fails
        $result = $this->controller(Aggro::class)
            ->execute('getYoutube', 'dQw4w9WgXcQ');

        $body = (string) $result->response()->getBody();

        $this->assertSame(404, $result->response()->getStatusCode());
        $this->assertStringNotContainsString('Added', $body);
        $this->assertStringContainsString('Could not look up https://www.youtube.com/watch?v=dQw4w9WgXcQ.', $body);
    }

    public function testGetLogCleanReturnsConfirmation(): void
    {
        // In CLI mode gate_check() returns true
        $result = $this->controller(Aggro::class)
            ->execute('getLogClean');

        $this->assertSame(200, $result->response()->getStatusCode());
        $this->assertSame('Old log entries cleared.', $result->response()->getBody());
        $this->assertFalse($result->response()->hasHeader('Location'));
    }

    public function testGetLogCleanReportsFailureWhenCleanFails(): void
    {
        // Without the log table, the clean fails
        $forge = Database::forge($this->DBGroup);
        $forge->renameTable('aggro_log', 'aggro_log_hidden');

        try {
            // In CLI mode gate_check() returns true
            $result = $this->controller(Aggro::class)
                ->execute('getLogClean');
        } finally {
            $forge->renameTable('aggro_log_hidden', 'aggro_log');
        }

        $this->assertSame(500, $result->response()->getStatusCode());
        $this->assertSame('Could not clear old log entries.', $result->response()->getBody());
        $this->assertFalse($result->response()->hasHeader('Location'));
    }

    public function testGetLogErrorShowsTheErrorLogs(): void
    {
        $this->withLogDirectory(function (string $directory): void {
            file_put_contents($directory . 'log-2026-01-01.log', 'CRITICAL - something broke');

            // In CLI mode gate_check() returns true
            $result = $this->controller(Aggro::class)
                ->execute('getLogError');

            $this->assertSame(200, $result->response()->getStatusCode());
            $this->assertStringContainsString('CRITICAL - something broke', (string) $result->response()->getBody());
        });
    }

    public function testGetLogErrorCleanRemovesOldLogs(): void
    {
        $this->withLogDirectory(function (string $directory): void {
            $older = $directory . 'log-2000-01-01.log';
            $today = $directory . 'log-' . date('Y-m-d') . '.log';
            file_put_contents($older, 'older');
            file_put_contents($today, 'today');

            // In CLI mode gate_check() returns true
            $result = $this->controller(Aggro::class)
                ->execute('getLogErrorClean');

            $this->assertSame(200, $result->response()->getStatusCode());
            $this->assertSame('Old error logs cleared.', $result->response()->getBody());
            $this->assertFalse($result->response()->hasHeader('Location'));
            $this->assertFileDoesNotExist($older);
            $this->assertFileExists($today);
        });
    }

    public function testGetInfoMethodSetsHeaders(): void
    {
        $result = $this->controller(Aggro::class)
            ->execute('getInfo');

        $response = $result->response();

        // Test that cache-related headers are set
        $this->assertTrue($response->hasHeader('Cache-Control'));
        $this->assertTrue($response->hasHeader('Pragma'));
        $this->assertTrue($response->hasHeader('Expires'));
    }

    public function testGetInfoOutputContainsVersionInfo(): void
    {
        $result = $this->controller(Aggro::class)
            ->execute('getInfo');

        $body = $result->response()->getBody();
        $this->assertStringContainsString('CI</span>', $body);
        $this->assertStringContainsString('PHP</span>', $body);
    }

    public function testGetInfoOutputContainsDeployInfo(): void
    {
        $result = $this->controller(Aggro::class)
            ->execute('getInfo');

        $body = $result->response()->getBody();
        $this->assertStringContainsString('<!-- deploy:release=', $body);
        $this->assertStringContainsString('Release</span>', $body);
        $this->assertStringContainsString('Env</span>', $body);
    }

    /**
     * An undeployed install carries DEPLOY_RELEASE="" and DEPLOY_TIMESTAMP=""
     * from .env-sample, which must read as "not deployed" rather than be
     * parsed as a timestamp.
     */
    public function testGetInfoTreatsEmptyDeployValuesAsLocal(): void
    {
        $original = [];

        foreach (['DEPLOY_RELEASE', 'DEPLOY_TIMESTAMP'] as $key) {
            $original[$key] = [$_ENV[$key] ?? null, $_SERVER[$key] ?? null, getenv($key)];
            $_ENV[$key]     = '';
            $_SERVER[$key]  = '';
            putenv($key . '=');
        }

        try {
            $body = $this->controller(Aggro::class)
                ->execute('getInfo')
                ->response()
                ->getBody();
        } finally {
            foreach ($original as $key => [$env, $server, $getenv]) {
                if ($env === null) {
                    unset($_ENV[$key]);
                } else {
                    $_ENV[$key] = $env;
                }

                if ($server === null) {
                    unset($_SERVER[$key]);
                } else {
                    $_SERVER[$key] = $server;
                }

                putenv($getenv === false ? $key : $key . '=' . $getenv);
            }
        }

        $this->assertStringContainsString('Release</span> relax, you&#039;re a local', $body);
        $this->assertStringContainsString('When</span> relax, you&#039;re a local', $body);
    }
}
