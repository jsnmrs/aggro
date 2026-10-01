<?php

/**
 * @file
 * Router for the local test server. Answers /status/<code> with that
 * HTTP status and an empty body.
 */
http_response_code((int) basename((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
