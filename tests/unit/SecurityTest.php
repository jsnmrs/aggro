<?php

namespace Tests\Unit;

use App\Services\ValidationService;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class SecurityTest extends DatabaseTestCase
{
    protected $validationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validationService = new ValidationService();
    }

    public function testSqlInjectionPrevention()
    {
        $maliciousInputs = [
            "'; DROP TABLE users; --",
            "1' OR '1'='1",
            "admin'--",
            '1; UPDATE users SET admin=1',
        ];

        foreach ($maliciousInputs as $input) {
            $sanitized = $this->validationService->sanitizeSlug($input);
            $this->assertStringNotContainsString("'", $sanitized);
            $this->assertStringNotContainsString(';', $sanitized);
            $this->assertStringNotContainsString('--', $sanitized);
        }
    }

    public function testVideoIdValidation()
    {
        // Valid YouTube IDs
        $this->assertNotNull($this->validationService->validateVideoId('dQw4w9WgXcQ', 'youtube'));

        // Invalid YouTube IDs
        $this->assertNull($this->validationService->validateVideoId('../../etc/passwd', 'youtube'));
        $this->assertNull($this->validationService->validateVideoId('<script>alert(1)</script>', 'youtube'));

        // Valid Vimeo IDs
        $this->assertNotNull($this->validationService->validateVideoId('123456789', 'vimeo'));

        // Invalid Vimeo IDs
        $this->assertNull($this->validationService->validateVideoId('abc123', 'vimeo'));
    }

    public function testGateKeyValidation()
    {
        // Valid gate keys
        $this->assertTrue($this->validationService->validateGateKey('valid-key_123'));
        $this->assertTrue($this->validationService->validateGateKey('ABCD1234'));

        // Invalid gate keys
        $this->assertFalse($this->validationService->validateGateKey(null));
        $this->assertFalse($this->validationService->validateGateKey(''));
        $this->assertFalse($this->validationService->validateGateKey('key with spaces'));
        $this->assertFalse($this->validationService->validateGateKey('key@domain.com'));
        $this->assertFalse($this->validationService->validateGateKey("key'with'quotes"));
    }

    public function testIntegerSanitization()
    {
        // Valid integers
        $this->assertSame(5, $this->validationService->sanitizeInt('5'));
        $this->assertSame(10, $this->validationService->sanitizeInt(10));

        // Invalid inputs should return min value
        $this->assertSame(0, $this->validationService->sanitizeInt('abc'));
        $this->assertSame(0, $this->validationService->sanitizeInt(null));
        $this->assertSame(0, $this->validationService->sanitizeInt(''));

        // Test with custom min/max
        $this->assertSame(1, $this->validationService->sanitizeInt(-5, 1, 10));
        $this->assertSame(10, $this->validationService->sanitizeInt(15, 1, 10));
        $this->assertSame(5, $this->validationService->sanitizeInt(5, 1, 10));
    }

    public function testSlugSanitization()
    {
        // Valid slug should remain unchanged
        $this->assertSame('valid-slug', $this->validationService->sanitizeSlug('valid-slug'));
        $this->assertSame('valid123', $this->validationService->sanitizeSlug('valid123'));

        // Remove special characters
        $this->assertSame('validslug', $this->validationService->sanitizeSlug('valid@slug'));
        $this->assertSame('validslug', $this->validationService->sanitizeSlug('valid slug'));

        // Multiple hyphens should be reduced to single
        $this->assertSame('valid-slug', $this->validationService->sanitizeSlug('valid---slug'));

        // Leading/trailing hyphens should be preserved (for YouTube video IDs)
        $this->assertSame('-valid-', $this->validationService->sanitizeSlug('-valid-'));

        // Test YouTube video ID with leading hyphen
        $this->assertSame('-Pp0Wg4gF54', $this->validationService->sanitizeSlug('-Pp0Wg4gF54'));

        // Test underscore support
        $this->assertSame('valid_slug', $this->validationService->sanitizeSlug('valid_slug'));
    }
}
