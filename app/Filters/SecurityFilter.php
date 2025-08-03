<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Services;

class SecurityFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Remove null bytes
        $_GET = $this->removeNullBytes($_GET);
        $_POST = $this->removeNullBytes($_POST);
        $_COOKIE = $this->removeNullBytes($_COOKIE);
        
        // Validate content type for POST requests
        if ($request->getMethod() === 'post') {
            $contentType = $request->getHeaderLine('Content-Type');
            if (!in_array($contentType, ['application/x-www-form-urlencoded', 'multipart/form-data', 'application/json'])) {
                return Services::response()->setStatusCode(415);
            }
        }
        
        return null;
    }
    
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Add security headers
        $response->setHeader('X-Content-Type-Options', 'nosniff');
        $response->setHeader('X-Frame-Options', 'DENY');
        $response->setHeader('X-XSS-Protection', '1; mode=block');
        
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