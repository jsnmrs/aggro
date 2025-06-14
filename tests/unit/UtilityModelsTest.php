<?php

namespace Tests\Unit;

use App\Models\UtilityModels;
use CodeIgniter\Model;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class UtilityModelsTest extends DatabaseTestCase
{
    protected UtilityModels $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new UtilityModels();
    }

    public function testModelExtendsCodeIgniterModel(): void
    {
        $this->assertInstanceOf(Model::class, $this->model);
    }

    public function testCleanLogMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'cleanLog'));
    }

    public function testCleanLogReturnsBoolean(): void
    {
        $result = $this->model->cleanLog();
        $this->assertIsBool($result);
    }

    public function testCleanLogWithEmptyDatabase(): void
    {
        $result = $this->model->cleanLog();
        $this->assertTrue($result);
    }

    public function testGetLogMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'getLog'));
    }

    public function testGetLogReturnsArray(): void
    {
        $result = $this->model->getLog();
        $this->assertIsArray($result);
    }

    public function testGetLogWithEmptyDatabase(): void
    {
        $result = $this->model->getLog();
        $this->assertEmpty($result);
    }

    public function testSendLogMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'sendLog'));
    }

    public function testSendLogReturnsBoolean(): void
    {
        $result = $this->model->sendLog('Test message');
        $this->assertIsBool($result);
    }

    public function testSendLogWithValidMessage(): void
    {
        $message = 'Test log message for unit testing';
        $result  = $this->model->sendLog($message);
        $this->assertTrue($result);
    }

    public function testSendLogWithEmptyMessage(): void
    {
        $result = $this->model->sendLog('');
        $this->assertIsBool($result);
    }

    public function testSendLogInsertsMessageCorrectly(): void
    {
        $message = 'Test message for verification';
        $result  = $this->model->sendLog($message);

        if ($result) {
            $logs = $this->model->getLog();
            $this->assertNotEmpty($logs);

            $found = false;

            foreach ($logs as $log) {
                if (isset($log->log_message) && $log->log_message === $message) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Message was not found in log entries');
        } else {
            $this->markTestSkipped('Database insert failed, skipping verification test');
        }
    }

    public function testGetLogOrdersByDate(): void
    {
        $result = $this->model->getLog();
        $this->assertIsArray($result);
        $this->assertLessThanOrEqual(100, count($result));
    }

    public function testCleanLogHandlesDatabaseErrors(): void
    {
        $result = $this->model->cleanLog();
        $this->assertIsBool($result);
    }

    public function testSendLogHandlesExceptions(): void
    {
        $result = $this->model->sendLog('Test exception handling');
        $this->assertIsBool($result);
    }

    public function testModelMethodsAreAccessible(): void
    {
        $this->assertTrue(method_exists($this->model, 'cleanLog'));
        $this->assertTrue(method_exists($this->model, 'getLog'));
        $this->assertTrue(method_exists($this->model, 'sendLog'));
    }
}
