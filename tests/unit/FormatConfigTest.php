<?php

use CodeIgniter\Format\JSONFormatter;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Format;

/**
 * Regression test for Sentry AGGRO-1X.
 *
 * The framework's JSONFormatter reads `$config->jsonEncodeDepth` directly and
 * passes it as `json_encode()`'s third argument, which PHP 8.x requires to be
 * an int. If the application's Config\Format omits the property, the value is
 * null and json_encode() throws a TypeError when formatting any JSON response
 * (notably error responses produced by CodeIgniter's ExceptionHandler).
 *
 * @internal
 */
final class FormatConfigTest extends CIUnitTestCase
{
    public function testJsonEncodeDepthIsAPositiveInt(): void
    {
        $config = new Format();

        $this->assertGreaterThanOrEqual(
            1,
            $config->jsonEncodeDepth,
            'Config\\Format::$jsonEncodeDepth must be a positive int — JSONFormatter'
            . ' passes it directly to json_encode() which requires a strict int on PHP 8.x.',
        );
    }

    public function testJsonFormatterCanEncodeWithoutTypeError(): void
    {
        $formatter = new JSONFormatter();

        $result = $formatter->format(['status' => 400, 'messages' => ['error' => 'bad']]);

        $this->assertIsString($result);
        $this->assertNotSame('', $result);
    }
}
