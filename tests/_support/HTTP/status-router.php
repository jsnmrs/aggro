<?php

/**
 * @file
 * Router for the local test server. Answers /status/<code> with that
 * HTTP status and an empty body, and /redirect/<code> with that status
 * and a Location header pointing at a watch page, so callers can see
 * a redirect without following it. /body/image answers with a JPEG and
 * /body/page with an HTML page, for callers that read the response body.
 */
$path = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$code = (int) basename($path);

if ($path === '/body/image') {
    header('Content-Type: image/jpeg');
    readfile(__DIR__ . '/../Images/ci-logo.jpeg');

    return;
}

if ($path === '/body/page') {
    header('Content-Type: text/html');
    echo '<html><body>Not an image</body></html>';

    return;
}

if (str_starts_with($path, '/redirect/')) {
    header('Location: /watch?v=test', true, $code);

    return;
}

http_response_code($code);
