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
