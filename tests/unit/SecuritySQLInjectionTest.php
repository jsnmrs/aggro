<?php

namespace Tests\Unit;

use App\Models\NewsModels;
use App\Models\UtilityModels;
use App\Models\YoutubeModels;
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

            // No site has that slug, and nothing was executed as SQL
            $this->assertNull($result);

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

                // Nothing matches, and nothing was executed as SQL
                $this->assertSame([], $result);

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
        $this->assertNull($result);

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
        $this->assertSame([], $result);

        // Test with negative values
        $result = $this->newsModels->streamPage(-1, -50);
        $this->assertSame([], $result);

        // Test with extremely large values
        $result = $this->newsModels->streamPage(PHP_INT_MAX, PHP_INT_MAX);
        $this->assertSame([], $result);
    }

    /**
     * Test that simple queries work correctly
     */
    public function testSimpleQueriesAreSafe()
    {
        // Test getSites method
        $result = $this->newsModels->getSites();
        $this->assertSame([], $result);

        // Test getSitesRecent method
        $result = $this->newsModels->getSitesRecent();
        $this->assertSame([], $result);

        // Test getLog method
        $result = $this->utilityModels->getLog();
        $this->assertSame([], $result);
    }
}
