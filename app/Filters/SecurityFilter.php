<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class SecurityFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Remove null bytes
        $_GET    = $this->removeNullBytes($_GET);
        $_POST   = $this->removeNullBytes($_POST);
        $_COOKIE = $this->removeNullBytes($_COOKIE);

        // Validate content type for POST requests. The framework reports
        // the method as the server received it, which is uppercase.
        if (strtoupper($request->getMethod()) === 'POST') {
            $contentType     = $request->getHeaderLine('Content-Type');
            $baseContentType = explode(';', $contentType)[0];
            $allowedTypes    = [
                'application/x-www-form-urlencoded',
                'multipart/form-data',
                'application/json',
            ];
            // A POST with no Content-Type has no body for PHP to parse,
            // so there is nothing to validate
            if ($contentType !== '' && ! in_array(trim($baseContentType), $allowedTypes, true)) {
                return Services::response()->setStatusCode(415);
            }
        }

        return null;
    }

    /**
     * Nothing runs after the controller. The security headers are set in
     * public/.htaccess.
     *
     * @param mixed|null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    private function removeNullBytes($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'removeNullBytes'], $data);
        }

        return is_string($data) ? str_replace(chr(0), '', $data) : $data;
    }
}
