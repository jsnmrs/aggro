<?php

namespace Tests\Unit;

use Config\Sentry;
use ReflectionClass;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class SecurityShellExecutionTest extends DatabaseTestCase
{
    protected $sentryConfig;
    protected $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sentryConfig = new Sentry();
        $this->reflection   = new ReflectionClass($this->sentryConfig);
    }

    /**
     * Test that shell_exec is no longer used anywhere in Sentry config
     */
    public function testNoShellExecInSentryConfig()
    {
        $sentryFilePath = APPPATH . 'Config/Sentry.php';
        $sentryContent  = file_get_contents($sentryFilePath);

        // Verify shell_exec is not present in the file
        $this->assertStringNotContainsString('shell_exec', $sentryContent, 'shell_exec should not be used in Sentry config');

        // Verify other dangerous functions are not used
        $dangerousFunctions = ['exec', 'system', 'passthru', 'eval', 'assert'];

        foreach ($dangerousFunctions as $func) {
            $this->assertStringNotContainsString($func . '(', $sentryContent, "{$func} should not be used in Sentry config");
        }
    }

    /**
     * Test getGitVersion method works without shell injection vulnerabilities
     */
    public function testGetGitVersionMethodExists()
    {
        $this->assertTrue($this->reflection->hasMethod('getGitVersion'));

        $getGitVersionMethod = $this->reflection->getMethod('getGitVersion');
        $getGitVersionMethod->setAccessible(true);

        // Should return a string (empty or version)
        $result = $getGitVersionMethod->invoke($this->sentryConfig);
        $this->assertIsString($result);
    }

    /**
     * Test getGitVersionWithProcOpen method for security
     */
    public function testGetGitVersionWithProcOpenSecurity()
    {
        $method = $this->reflection->getMethod('getGitVersionWithProcOpen');
        $method->setAccessible(true);

        // Should return a string (empty or version)
        $result = $method->invoke($this->sentryConfig);
        $this->assertIsString($result);

        // Should not contain any injection attempts
        $this->assertStringNotContainsString(';', $result);
        $this->assertStringNotContainsString('&&', $result);
        $this->assertStringNotContainsString('||', $result);
        $this->assertStringNotContainsString('`', $result);
        $this->assertStringNotContainsString('$(', $result);
    }

    /**
     * Test getGitVersionFromFiles method for security
     */
    public function testGetGitVersionFromFilesSecurity()
    {
        $method = $this->reflection->getMethod('getGitVersionFromFiles');
        $method->setAccessible(true);

        // Should return a string (empty or version)
        $result = $method->invoke($this->sentryConfig);
        $this->assertIsString($result);

        // Should not contain any injection attempts
        $this->assertStringNotContainsString(';', $result);
        $this->assertStringNotContainsString('&&', $result);
        $this->assertStringNotContainsString('||', $result);
        $this->assertStringNotContainsString('`', $result);
        $this->assertStringNotContainsString('$(', $result);

        // Should be empty or a valid git hash (7+ alphanumeric characters)
        if (! empty($result)) {
            $this->assertTrue(ctype_alnum($result) || preg_match('/^[a-f0-9-\.]+$/i', $result));
            $this->assertLessThanOrEqual(50, strlen($result)); // Reasonable max length
        }
    }

    /**
     * Test that Sentry config initializes properly without shell injection
     */
    public function testSentryConfigInitialization()
    {
        // Create new instance to trigger constructor
        $config = new Sentry();

        // Should have a release property set
        $this->assertObjectHasProperty('release', $config);
        $this->assertIsString($config->release);

        // Release should not contain injection attempts
        $this->assertStringNotContainsString(';', $config->release);
        $this->assertStringNotContainsString('&&', $config->release);
        $this->assertStringNotContainsString('||', $config->release);
        $this->assertStringNotContainsString('`', $config->release);
        $this->assertStringNotContainsString('$(', $config->release);

        // Should be either empty, a timestamp, or a git version/tag
        if (! empty($config->release)) {
            $isTimestamp  = preg_match('/^\d{4}-\d{2}-\d{2}-\d{6}$/', $config->release);
            $isGitVersion = preg_match('/^[a-f0-9-\.v]+$/i', $config->release); // Allow version tags like v1.0.0
            $isGitTag     = preg_match('/^[a-zA-Z0-9\.\-_]+$/', $config->release); // Allow git tags/branches
            $this->assertTrue($isTimestamp || $isGitVersion || $isGitTag, 'Release should be timestamp, git version, or git tag format. Got: ' . $config->release);
        }
    }

    /**
     * Test that proc_open usage is secure by checking method implementation
     */
    public function testProcOpenSecurityImplementation()
    {
        $sentryFilePath = APPPATH . 'Config/Sentry.php';
        $sentryContent  = file_get_contents($sentryFilePath);

        // Verify proc_open is used with command array (secure)
        $this->assertStringContainsString("['git', 'describe', '--tags', '--always']", $sentryContent);

        // Verify no string concatenation in proc_open calls
        $procOpenPattern = '/proc_open\s*\(\s*[\'"][^\'"].*[\'"]/';
        $this->assertDoesNotMatchRegularExpression($procOpenPattern, $sentryContent, 'proc_open should use command array, not string');
    }

    /**
     * Test file reading security - ensure no path traversal
     */
    public function testFileReadingSecurity()
    {
        $method = $this->reflection->getMethod('getGitVersionFromFiles');
        $method->setAccessible(true);

        // Mock a scenario and verify it handles paths securely
        $result = $method->invoke($this->sentryConfig);

        // Method should not fail even if .git doesn't exist
        $this->assertIsString($result);

        // Verify the method uses ROOTPATH constant (not user input)
        $sentryFilePath = APPPATH . 'Config/Sentry.php';
        $sentryContent  = file_get_contents($sentryFilePath);
        $this->assertStringContainsString('ROOTPATH . \'.git\'', $sentryContent);
        $this->assertStringNotContainsString('$_GET', $sentryContent);
        $this->assertStringNotContainsString('$_POST', $sentryContent);
        $this->assertStringNotContainsString('$_REQUEST', $sentryContent);
    }

    /**
     * Test error handling doesn't expose sensitive information
     */
    public function testErrorHandlingSecurity()
    {
        // Force an error condition by making proc_open fail
        // We can't easily mock this, but we can verify error handling structure
        $sentryFilePath = APPPATH . 'Config/Sentry.php';
        $sentryContent  = file_get_contents($sentryFilePath);

        // Verify proper error handling exists
        $this->assertStringContainsString('catch (InvalidArgumentException|RuntimeException $e)', $sentryContent);
        $this->assertStringContainsString('log_message(', $sentryContent);

        // Verify no sensitive data is exposed in error messages
        $this->assertStringNotContainsString('echo', $sentryContent);
        $this->assertStringNotContainsString('print', $sentryContent);
        $this->assertStringNotContainsString('var_dump', $sentryContent);
        $this->assertStringNotContainsString('print_r', $sentryContent);
    }

    /**
     * Test environment variable handling is secure
     */
    public function testEnvironmentVariableHandling()
    {
        // Test with different environment scenarios
        $originalRelease = $_ENV['SENTRY_RELEASE'] ?? null;

        try {
            // Test with empty env var (should use git detection)
            unset($_ENV['SENTRY_RELEASE']);
            $config1 = new Sentry();
            $this->assertIsString($config1->release);

            // Test with set env var (should use env var)
            $_ENV['SENTRY_RELEASE'] = 'test-release-1.0.0';
            $config2                = new Sentry();
            $this->assertSame('test-release-1.0.0', $config2->release);

            // Test with malicious env var (should be safe)
            $_ENV['SENTRY_RELEASE'] = 'test; rm -rf /';
            $config3                = new Sentry();
            $this->assertSame('test; rm -rf /', $config3->release); // Should store as-is but not execute
        } finally {
            // Restore original value
            if ($originalRelease !== null) {
                $_ENV['SENTRY_RELEASE'] = $originalRelease;
            } else {
                unset($_ENV['SENTRY_RELEASE']);
            }
        }
    }
}
