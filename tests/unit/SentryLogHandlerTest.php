<?php

namespace Tests\Unit;

use App\Libraries\SentryLogHandler;
use CodeIgniter\Test\CIUnitTestCase;
use ReflectionClass;

/**
 * @internal
 */
final class SentryLogHandlerTest extends CIUnitTestCase
{
    private SentryLogHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new SentryLogHandler([]);
    }

    public function testCanHandleCritical(): void
    {
        $this->assertTrue($this->handler->canHandle('critical'));
    }

    public function testCanHandleAlert(): void
    {
        $this->assertTrue($this->handler->canHandle('alert'));
    }

    public function testCanHandleEmergency(): void
    {
        $this->assertTrue($this->handler->canHandle('emergency'));
    }

    public function testCanHandleError(): void
    {
        $this->assertTrue($this->handler->canHandle('error'));
    }

    public function testCannotHandleInfo(): void
    {
        $this->assertFalse($this->handler->canHandle('info'));
    }

    public function testCannotHandleDebug(): void
    {
        $this->assertFalse($this->handler->canHandle('debug'));
    }

    public function testCannotHandleWarning(): void
    {
        $this->assertFalse($this->handler->canHandle('warning'));
    }

    public function testCannotHandleNotice(): void
    {
        $this->assertFalse($this->handler->canHandle('notice'));
    }

    public function testMapLogLevelToSentry(): void
    {
        $reflection = new ReflectionClass($this->handler);
        $method     = $reflection->getMethod('mapLogLevelToSentry');
        $method->setAccessible(true);

        $this->assertSame('fatal', $method->invoke($this->handler, 'emergency'));
        $this->assertSame('fatal', $method->invoke($this->handler, 'alert'));
        $this->assertSame('error', $method->invoke($this->handler, 'critical'));
        $this->assertSame('error', $method->invoke($this->handler, 'error'));
        $this->assertSame('warning', $method->invoke($this->handler, 'warning'));
        $this->assertSame('info', $method->invoke($this->handler, 'notice'));
        $this->assertSame('info', $method->invoke($this->handler, 'info'));
        $this->assertSame('debug', $method->invoke($this->handler, 'debug'));
        $this->assertSame('info', $method->invoke($this->handler, 'unknown'));
    }

    public function testCleanErrorMessageRemovesTimestamp(): void
    {
        $reflection = new ReflectionClass($this->handler);
        $method     = $reflection->getMethod('cleanErrorMessage');
        $method->setAccessible(true);

        $message = '2024-01-15 10:30:00 --> ERROR - Something went wrong';
        $result  = $method->invoke($this->handler, $message);

        $this->assertSame('Something went wrong', $result);
    }

    public function testCleanErrorMessageExtractsErrorBeforeStackTrace(): void
    {
        $reflection = new ReflectionClass($this->handler);
        $method     = $reflection->getMethod('cleanErrorMessage');
        $method->setAccessible(true);

        $message = 'Division by zero in /app/file.php on line 42';
        $result  = $method->invoke($this->handler, $message);

        $this->assertSame('Division by zero', $result);
    }

    public function testCleanErrorMessageReturnsPlainMessage(): void
    {
        $reflection = new ReflectionClass($this->handler);
        $method     = $reflection->getMethod('cleanErrorMessage');
        $method->setAccessible(true);

        $message = 'Simple error message';
        $result  = $method->invoke($this->handler, $message);

        $this->assertSame('Simple error message', $result);
    }

    public function testHandleFiltersDisallowedCharacterErrors(): void
    {
        // Messages about disallowed URI characters should be silently filtered
        $result = $this->handler->handle('error', 'The URI you submitted has disallowed characters');

        $this->assertTrue($result);
    }

    public function testHandleReturnsTrue(): void
    {
        // In test environment (not production/development), handle returns true early
        $result = $this->handler->handle('error', 'Test error message');

        $this->assertTrue($result);
    }

    public function testSetDateFormatReturnsSelf(): void
    {
        $result = $this->handler->setDateFormat('Y-m-d');

        $this->assertSame($this->handler, $result);
    }

    public function testHandleWithArrayMessage(): void
    {
        $result = $this->handler->handle('error', ['key' => 'value']);

        $this->assertTrue($result);
    }

    public function testHandleWithErrorPrefixedMessage(): void
    {
        $result = $this->handler->handle('error', 'ERROR - Something failed');

        $this->assertTrue($result);
    }
}
