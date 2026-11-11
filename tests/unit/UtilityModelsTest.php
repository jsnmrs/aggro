<?php

namespace Tests\Unit;

use App\Models\UtilityModels;
use Config\Database;
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

        if (! $this->db->tableExists('aggro_log')) {
            $this->markTestSkipped('Database table aggro_log not available in test environment');
        }

        $this->model = new UtilityModels();
    }

    public function testCleanLogWithEmptyDatabaseLogsZeroDeletions(): void
    {
        $this->assertTrue($this->model->cleanLog());

        $this->seeInDatabase('aggro_log', ['log_message' => '0 log entries, older than 1 day deleted.']);
    }

    public function testGetLogWithEmptyDatabase(): void
    {
        $this->assertSame([], $this->model->getLog());
    }

    public function testSendLogWithEmptyMessage(): void
    {
        $this->assertTrue($this->model->sendLog(''));

        $this->seeInDatabase('aggro_log', ['log_message' => '']);
    }

    public function testSendLogStoresTheMessage(): void
    {
        $this->assertTrue($this->model->sendLog('Test message for verification'));

        $this->seeInDatabase('aggro_log', ['log_message' => 'Test message for verification']);
    }

    public function testGetLogReturnsNewestEntriesFirst(): void
    {
        $this->insertLogEntry('-3 days', 'Older entry');
        $this->insertLogEntry('-1 hour', 'Recent entry');
        $this->insertLogEntry('-2 days', 'Old entry');

        $messages = array_column($this->model->getLog(), 'log_message');

        $this->assertSame(['Recent entry', 'Old entry', 'Older entry'], $messages);
    }

    public function testCleanLogWithOldEntries(): void
    {
        $this->insertLogEntry('-3 days', 'Older entry');
        $this->insertLogEntry('-2 days', 'Old entry');
        $this->insertLogEntry('-1 hour', 'Recent entry');

        $this->assertTrue($this->model->cleanLog());

        $messages = array_column($this->db->table('aggro_log')->get()->getResultArray(), 'log_message');

        $this->assertNotContains('Older entry', $messages);
        $this->assertNotContains('Old entry', $messages);
        $this->assertContains('Recent entry', $messages);
    }

    public function testCleanLogReturnsFalseWhenDeleteFails(): void
    {
        // Without the log table, the delete fails
        $forge = Database::forge($this->DBGroup);
        $forge->renameTable('aggro_log', 'aggro_log_hidden');

        try {
            $this->assertFalse($this->model->cleanLog());
        } finally {
            $forge->renameTable('aggro_log_hidden', 'aggro_log');
        }
    }

    public function testGetLogReturnsAtMostOneHundredEntries(): void
    {
        for ($i = 0; $i < 101; $i++) {
            $this->insertLogEntry('-' . $i . ' minutes', 'Entry ' . $i);
        }

        $this->assertCount(100, $this->model->getLog());
    }

    public function testSendLogReturnsFalseForNullMessage(): void
    {
        // The message column does not accept null, so the insert fails
        $this->assertFalse($this->model->sendLog(null));

        $this->assertSame(0, $this->db->table('aggro_log')->countAllResults());
    }

    public function testSendLogStoresALongMessageWhole(): void
    {
        $longMessage = str_repeat('This is a long test message. ', 100);

        $this->assertTrue($this->model->sendLog($longMessage));

        $row = $this->db->table('aggro_log')->get()->getRowArray();
        $this->assertSame($longMessage, $row['log_message']);
    }

    public function testSendLogReturnsFalseWhenInsertFails(): void
    {
        // Without the log table, the insert fails
        $forge = Database::forge($this->DBGroup);
        $forge->renameTable('aggro_log', 'aggro_log_hidden');

        try {
            $this->assertFalse($this->model->sendLog('Test message'));
        } finally {
            $forge->renameTable('aggro_log_hidden', 'aggro_log');
        }
    }

    public function testCleanLogLogsResults(): void
    {
        $this->insertLogEntry('-3 days', 'Older entry');
        $this->insertLogEntry('-2 days', 'Old entry');
        $this->insertLogEntry('-1 hour', 'Recent entry');

        $this->model->cleanLog();

        $this->seeInDatabase('aggro_log', ['log_message' => '2 log entries, older than 1 day deleted.']);
    }

    /**
     * Insert a log entry dated relative to now.
     */
    private function insertLogEntry(string $when, string $message): void
    {
        $this->db->table('aggro_log')->insert([
            'log_date'    => date('Y-m-d H:i:s', strtotime($when)),
            'log_message' => $message,
        ]);
    }
}
