<?php

namespace Tests\Unit;

use Tests\Support\DatabaseTestCase;
use App\Services\ValidationService;

class SecurityTest extends DatabaseTestCase
{
    protected $validationService;
    
    public function setUp(): void
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
            "1; UPDATE users SET admin=1",
        ];
        
        foreach ($maliciousInputs as $input) {
            $sanitized = $this->validationService->sanitizeSlug($input);
            $this->assertStringNotContainsString("'", $sanitized);
            $this->assertStringNotContainsString(";", $sanitized);
            $this->assertStringNotContainsString("--", $sanitized);
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
        $this->assertEquals(5, $this->validationService->sanitizeInt('5'));
        $this->assertEquals(10, $this->validationService->sanitizeInt(10));
        
        // Invalid inputs should return min value
        $this->assertEquals(0, $this->validationService->sanitizeInt('abc'));
        $this->assertEquals(0, $this->validationService->sanitizeInt(null));
        $this->assertEquals(0, $this->validationService->sanitizeInt(''));
        
        // Test with custom min/max
        $this->assertEquals(1, $this->validationService->sanitizeInt(-5, 1, 10));
        $this->assertEquals(10, $this->validationService->sanitizeInt(15, 1, 10));
        $this->assertEquals(5, $this->validationService->sanitizeInt(5, 1, 10));
    }
    
    public function testSlugSanitization()
    {
        // Valid slug should remain unchanged
        $this->assertEquals('valid-slug', $this->validationService->sanitizeSlug('valid-slug'));
        $this->assertEquals('valid123', $this->validationService->sanitizeSlug('valid123'));
        
        // Remove special characters
        $this->assertEquals('validslug', $this->validationService->sanitizeSlug('valid@slug'));
        $this->assertEquals('validslug', $this->validationService->sanitizeSlug('valid slug'));
        
        // Multiple hyphens should be reduced to single
        $this->assertEquals('valid-slug', $this->validationService->sanitizeSlug('valid---slug'));
        
        // Leading/trailing hyphens should be removed
        $this->assertEquals('valid', $this->validationService->sanitizeSlug('-valid-'));
    }
}