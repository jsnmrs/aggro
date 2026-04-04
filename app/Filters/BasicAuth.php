<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * HTTP Basic Authentication filter for the development environment.
 *
 * Prompts for credentials when BASIC_AUTH_USER and BASIC_AUTH_PASS
 * are set in .env and the app is running in the development environment.
 * Fails open: skips auth when env vars are missing.
 */
class BasicAuth implements FilterInterface
{
    private string $environment;

    public function __construct(?string $environment = null)
    {
        $this->environment = $environment ?? env('CI_ENVIRONMENT', 'production');
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        if ($this->environment !== 'development') {
            return null;
        }

        if ($this->isCli()) {
            return null;
        }

        $expectedUser = env('BASIC_AUTH_USER');
        $expectedPass = env('BASIC_AUTH_PASS');

        // Fail-open: skip auth when credentials are not configured
        if (empty($expectedUser) || empty($expectedPass)) {
            return null;
        }

        $providedUser = $_SERVER['PHP_AUTH_USER'] ?? '';
        $providedPass = $_SERVER['PHP_AUTH_PW'] ?? '';

        if (hash_equals($expectedUser, $providedUser) && hash_equals($expectedPass, $providedPass)) {
            return null;
        }

        $response = Services::response();
        $response->setStatusCode(401);
        $response->setHeader('WWW-Authenticate', 'Basic realm="Development"');
        $response->setBody('Unauthorized');

        return $response;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    protected function isCli(): bool
    {
        return is_cli();
    }
}
