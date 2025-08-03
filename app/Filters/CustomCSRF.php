<?php

namespace App\Filters;

use CodeIgniter\Filters\CSRF;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Custom CSRF filter that exempts CLI and authenticated gate requests
 */
class CustomCSRF extends CSRF
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param array|null $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Skip CSRF check for CLI requests
        if (is_cli()) {
            return;
        }

        // Skip CSRF check for requests with valid gate parameter
        helper('aggro');
        if (gate_check()) {
            return;
        }

        // Otherwise, run normal CSRF check
        return parent::before($request, $arguments);
    }
}
