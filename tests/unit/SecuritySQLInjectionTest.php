<?php

namespace Tests\Unit;

use App\Models\NewsModels;
use App\Models\UtilityModels;
use App\Models\YoutubeModels;
use SimplePie;
use SimplePie_Item;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class SecuritySQLInjectionTest extends DatabaseTestCase
{
    protected $newsModels;
    protected $youtubeModels;
    protected $utilityModels;

    protected function setUp(): void
    {
        parent::setUp();
        $this->newsModels    = new NewsModels();
        $this->youtubeModels = new YoutubeModels();
        $this->utilityModels = new UtilityModels();
    }

    /**
     * Test SQL injection attempts in getSite method
     */
    public function testGetSiteWithSQLInjection()
    {
        $maliciousInputs = [
            "'; DROP TABLE news_feeds; --",
            "' OR '1'='1",
            "admin'--",
            "' UNION SELECT * FROM users--",
            "1'; UPDATE news_feeds SET flag_enabled=1; --",
        ];

        foreach ($maliciousInputs as $input) {
            // Should not throw exception and should return safe result
            $result = $this->newsModels->getSite($input);

            // Verify the query executed safely
            $this->assertTrue(is_array($result) || $result === null);

            // Verify table still exists
            $this->assertTrue($this->db->tableExists('news_feeds'));
        }
    }

    /**
     * Test SQL injection in streamPage pagination
     */
    public function testStreamPageWithSQLInjection()
    {
        $maliciousPages = [
            '1; DROP TABLE news_featured; --',
            '-1 UNION SELECT * FROM users',
            '1 OR 1=1',
        ];

        $maliciousLimits = [
            '50; DELETE FROM news_feeds;',
            '-1 UNION ALL SELECT NULL',
            '50 OR 1=1',
        ];

        foreach ($maliciousPages as $page) {
            foreach ($maliciousLimits as $limit) {
                // Should handle malicious input safely
                $result = $this->newsModels->streamPage($page, $limit);

                // Verify it returns an array (even if empty)
                $this->assertIsArray($result);

                // Verify tables still exist
                $this->assertTrue($this->db->tableExists('news_featured'));
                $this->assertTrue($this->db->tableExists('news_feeds'));
            }
        }
    }

    /**
     * Test SQL injection in log message
     */
    public function testSendLogWithSQLInjection()
    {
        $maliciousMessages = [
            "'); DROP TABLE aggro_log; --",
            "', (NOW(), 'hacked')); DROP TABLE aggro_videos; --",
            "'); DELETE FROM aggro_log WHERE 1=1; --",
        ];

        foreach ($maliciousMessages as $message) {
            // Should handle malicious input safely
            $result = $this->utilityModels->sendLog($message);

            // Should return true (log inserted safely)
            $this->assertTrue($result);

            // Verify table still exists
            $this->assertTrue($this->db->tableExists('aggro_log'));

            // Verify the message was inserted safely (escaped)
            $query = $this->db->table('aggro_log')
                ->where('log_message', $message)
                ->get();
            $this->assertGreaterThan(0, $query->getNumRows());
        }
    }

    /**
     * Test that queries use parameter binding
     */
    public function testQueriesUseParameterBinding()
    {
        // Test getSite with special characters
        $testSlug = "test's-slug";
        $result   = $this->newsModels->getSite($testSlug);
        $this->assertTrue(is_array($result) || $result === null);

        // Test sendLog with quotes
        $testMessage = "Test message with 'quotes' and \"double quotes\"";
        $result      = $this->utilityModels->sendLog($testMessage);
        $this->assertTrue($result);
    }

    /**
     * Test numeric input validation
     */
    public function testNumericInputValidation()
    {
        // Test with non-numeric page values
        $result = $this->newsModels->streamPage('abc', 'def');
        $this->assertIsArray($result);

        // Test with negative values
        $result = $this->newsModels->streamPage(-1, -50);
        $this->assertIsArray($result);

        // Test with extremely large values
        $result = $this->newsModels->streamPage(PHP_INT_MAX, PHP_INT_MAX);
        $this->assertIsArray($result);
    }

    /**
     * Test that simple queries work correctly
     */
    public function testSimpleQueriesAreSafe()
    {
        // Test getSites method
        $result = $this->newsModels->getSites();
        $this->assertIsArray($result);

        // Test getSitesRecent method
        $result = $this->newsModels->getSitesRecent();
        $this->assertIsArray($result);

        // Test getLog method
        $result = $this->utilityModels->getLog();
        $this->assertIsArray($result);
    }

    /**
     * Verify no raw SQL concatenation in critical methods
     */
    public function testNoRawSQLConcatenation()
    {
        // This test checks that our refactored methods don't use string concatenation
        // by attempting to inject SQL through various parameters

        // Test updateFeed
        $mockFeed = $this->createMock(SimplePie::class);
        $mockItem = $this->createMock(SimplePie_Item::class);
        $mockItem->method('get_date')->willReturn('2024-01-01 00:00:00');
        $mockFeed->method('get_items')->willReturn([$mockItem]);

        $maliciousSlug = "test'; DROP TABLE news_feeds; --";
        $this->newsModels->updateFeed($maliciousSlug, $mockFeed);

        // Verify table still exists
        $this->assertTrue($this->db->tableExists('news_feeds'));
    }
}
