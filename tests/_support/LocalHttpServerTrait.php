<?php

namespace Tests\Support;

use RuntimeException;

/**
 * Serves HTTP responses from a throwaway local server, so tests can
 * exercise real status codes without reaching an outside host.
 *
 * The server starts the first time a test asks for a URL and stops
 * once the test class is done.
 */
trait LocalHttpServerTrait
{
    /**
     * @var resource|null
     */
    private static $localHttpServer;

    private static int $localHttpServerPort = 0;

    public static function tearDownAfterClass(): void
    {
        if (is_resource(self::$localHttpServer)) {
            proc_terminate(self::$localHttpServer);
            proc_close(self::$localHttpServer);
        }

        self::$localHttpServer = null;

        parent::tearDownAfterClass();
    }

    /**
     * Build a URL on the local server, such as /status/404 for a 404 response.
     */
    private function localUrl(string $path): string
    {
        if (! is_resource(self::$localHttpServer)) {
            self::startLocalHttpServer();
        }

        return 'http://127.0.0.1:' . self::$localHttpServerPort . $path;
    }

    /**
     * Start PHP's built-in server on a free loopback port and wait for it
     * to accept connections.
     */
    private static function startLocalHttpServer(): void
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        $port   = (int) substr(strrchr(stream_socket_get_name($socket, false), ':'), 1);
        fclose($socket);

        $server = proc_open(
            [PHP_BINARY, '-S', '127.0.0.1:' . $port, __DIR__ . '/HTTP/status-router.php'],
            [['file', '/dev/null', 'r'], ['file', '/dev/null', 'w'], ['file', '/dev/null', 'w']],
            $pipes,
        );

        if ($server === false) {
            throw new RuntimeException('Failed to start the local test server.');
        }

        self::$localHttpServer     = $server;
        self::$localHttpServerPort = $port;

        // Up to 2 seconds for the server to accept a connection
        for ($attempt = 0; $attempt < 100; $attempt++) {
            $connection = @fsockopen('127.0.0.1', $port);

            if ($connection !== false) {
                fclose($connection);

                return;
            }

            usleep(20000);
        }

        throw new RuntimeException('The local test server did not start on port ' . $port . '.');
    }
}
