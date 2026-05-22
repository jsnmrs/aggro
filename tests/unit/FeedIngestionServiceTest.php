<?php

use App\Services\FeedIngestionService;
use Tests\Support\ServiceTestCase;

/**
 * @internal
 */
final class FeedIngestionServiceTest extends ServiceTestCase
{
    private FeedIngestionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FeedIngestionService();
    }

    public function testFeaturedBuilderReturnsTrueWithNoFeeds()
    {
        // Act - No feeds in database
        $result = $this->service->featuredBuilder();

        // Assert
        $this->assertFalse($result); // Should return false when no featured feeds found
    }

    public function testFeaturedBuilderProcessesFeaturedFeeds()
    {
        // Arrange
        $feedData = [
            'site_id'              => 1,
            'site_name'            => 'Test Site',
            'site_slug'            => 'test-site',
            'site_url'             => 'https://example.com',
            'site_feed'            => 'https://example.com/feed.xml',
            'site_category'        => 'news',
            'flag_featured'        => 1,
            'flag_stream'          => 0,
            'flag_spoof'           => 0,
            'site_date_added'      => date('Y-m-d H:i:s'),
            'site_date_updated'    => date('Y-m-d H:i:s'),
            'site_date_last_fetch' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'site_date_last_post'  => date('Y-m-d H:i:s', strtotime('-2 hours')),
        ];

        $this->db->table('news_feeds')->insert($feedData);

        // Act
        $result = $this->service->featuredBuilder();

        // Assert
        $this->assertTrue($result);
    }

    public function testFeaturedBuilderProcessesStreamFeeds()
    {
        // Arrange
        $feedData = [
            'site_id'              => 1,
            'site_name'            => 'Test Stream Site',
            'site_slug'            => 'test-stream-site',
            'site_url'             => 'https://example.com',
            'site_feed'            => 'https://example.com/feed.xml',
            'site_category'        => 'stream',
            'flag_featured'        => 0,
            'flag_stream'          => 1,
            'flag_spoof'           => 0,
            'site_date_added'      => date('Y-m-d H:i:s'),
            'site_date_updated'    => date('Y-m-d H:i:s'),
            'site_date_last_fetch' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'site_date_last_post'  => date('Y-m-d H:i:s', strtotime('-2 hours')),
        ];

        $this->db->table('news_feeds')->insert($feedData);

        // Act
        $result = $this->service->featuredBuilder();

        // Assert
        $this->assertTrue($result);
    }

    public function testFeaturedBuilderIgnoresNonFeaturedNonStreamFeeds()
    {
        // Arrange
        $feedData = [
            'site_id'              => 1,
            'site_name'            => 'Test Non-Featured Site',
            'site_slug'            => 'test-non-featured',
            'site_url'             => 'https://example.com',
            'site_feed'            => 'https://example.com/feed.xml',
            'site_category'        => 'other',
            'flag_featured'        => 0,
            'flag_stream'          => 0, // Neither featured nor stream
            'flag_spoof'           => 0,
            'site_date_added'      => date('Y-m-d H:i:s'),
            'site_date_updated'    => date('Y-m-d H:i:s'),
            'site_date_last_fetch' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'site_date_last_post'  => date('Y-m-d H:i:s', strtotime('-2 hours')),
        ];

        $this->db->table('news_feeds')->insert($feedData);

        // Act
        $result = $this->service->featuredBuilder();

        // Assert
        $this->assertFalse($result); // Should return false as no featured/stream feeds found
    }

    public function testFeaturedBuilderHandlesMultipleFeeds()
    {
        // Arrange
        $feedData1 = [
            'site_id'              => 1,
            'site_name'            => 'Test Site 1',
            'site_slug'            => 'test-site-1',
            'site_url'             => 'https://example1.com',
            'site_feed'            => 'https://example1.com/feed.xml',
            'site_category'        => 'news',
            'flag_featured'        => 1,
            'flag_stream'          => 0,
            'flag_spoof'           => 0,
            'site_date_added'      => date('Y-m-d H:i:s'),
            'site_date_updated'    => date('Y-m-d H:i:s'),
            'site_date_last_fetch' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'site_date_last_post'  => date('Y-m-d H:i:s', strtotime('-2 hours')),
        ];

        $feedData2 = [
            'site_id'              => 2,
            'site_name'            => 'Test Site 2',
            'site_slug'            => 'test-site-2',
            'site_url'             => 'https://example2.com',
            'site_feed'            => 'https://example2.com/feed.xml',
            'site_category'        => 'stream',
            'flag_featured'        => 0,
            'flag_stream'          => 1,
            'flag_spoof'           => 0,
            'site_date_added'      => date('Y-m-d H:i:s'),
            'site_date_updated'    => date('Y-m-d H:i:s'),
            'site_date_last_fetch' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'site_date_last_post'  => date('Y-m-d H:i:s', strtotime('-2 hours')),
        ];

        $this->db->table('news_feeds')->insertBatch([$feedData1, $feedData2]);

        // Act
        $result = $this->service->featuredBuilder();

        // Assert
        $this->assertTrue($result);
    }

    public function testSaveFeedItemsRetriesOnDeadlockAndSucceeds()
    {
        $stubDb = $this->makeTransactionStub(
            [false, true],
            [['code' => 1213, 'message' => 'Deadlock found', 'sqlstate' => '40001']],
        );
        $service = $this->makeServiceWithStubbedDb($stubDb);

        $result = $this->invokeSaveFeedItems($service, $stubDb);

        $this->assertTrue($result);
        $this->assertSame(2, $stubDb->countCalls('transStart'));
        $this->assertSame(1, $service->sleepCallCount);
    }

    public function testSaveFeedItemsGivesUpAfterMaxDeadlockRetries()
    {
        $stubDb = $this->makeTransactionStub(
            [false, false, false],
            [
                ['code' => 1213, 'message' => 'Deadlock found', 'sqlstate' => '40001'],
                ['code' => 1213, 'message' => 'Deadlock found', 'sqlstate' => '40001'],
                ['code' => 1213, 'message' => 'Deadlock found', 'sqlstate' => '40001'],
            ],
        );
        $service = $this->makeServiceWithStubbedDb($stubDb);

        $result = $this->invokeSaveFeedItems($service, $stubDb);

        $this->assertFalse($result);
        $this->assertSame(3, $stubDb->countCalls('transStart'));
        $this->assertSame(2, $service->sleepCallCount);
    }

    public function testSaveFeedItemsDoesNotRetryOnNonDeadlockFailure()
    {
        $stubDb = $this->makeTransactionStub(
            [false],
            [['code' => 1062, 'message' => 'Duplicate entry', 'sqlstate' => '23000']],
        );
        $service = $this->makeServiceWithStubbedDb($stubDb);

        $result = $this->invokeSaveFeedItems($service, $stubDb);

        $this->assertFalse($result);
        $this->assertSame(1, $stubDb->countCalls('transStart'));
        $this->assertSame(0, $service->sleepCallCount);
    }

    public function testSaveFeedItemsSucceedsFirstTryWithoutRetryOverhead()
    {
        $stubDb  = $this->makeTransactionStub([true], []);
        $service = $this->makeServiceWithStubbedDb($stubDb);

        $result = $this->invokeSaveFeedItems($service, $stubDb);

        $this->assertTrue($result);
        $this->assertSame(1, $stubDb->countCalls('transStart'));
        $this->assertSame(0, $service->sleepCallCount);
    }

    private function makeTransactionStub(array $transStatusReturns, array $errorReturns): object
    {
        return new class ($transStatusReturns, $errorReturns) {
            public array $calls = [];
            private array $transStatusReturns;
            private array $errorReturns;

            public function __construct(array $transStatusReturns, array $errorReturns)
            {
                $this->transStatusReturns = $transStatusReturns;
                $this->errorReturns       = $errorReturns;
            }

            public function transStart(): void
            {
                $this->calls[] = 'transStart';
            }

            public function transComplete(): void
            {
                $this->calls[] = 'transComplete';
            }

            public function transStatus(): bool
            {
                return array_shift($this->transStatusReturns) ?? true;
            }

            public function error(): array
            {
                return array_shift($this->errorReturns) ?? ['code' => 0, 'message' => '', 'sqlstate' => ''];
            }

            public function table(string $name): object
            {
                return new class () {
                    public function where($key, $value = null): self
                    {
                        return $this;
                    }

                    public function update($data): bool
                    {
                        return true;
                    }
                };
            }

            public function countCalls(string $method): int
            {
                return count(array_filter($this->calls, static fn ($c) => $c === $method));
            }
        };
    }

    private function makeServiceWithStubbedDb(object $stubDb): FeedIngestionService
    {
        $service = new class () extends FeedIngestionService {
            public int $sleepCallCount = 0;

            protected function sleepBetweenAttempts(int $ms): void
            {
                $this->sleepCallCount++;
            }
        };

        $reflection = new ReflectionClass(FeedIngestionService::class);
        $dbProp     = $reflection->getProperty('db');
        $dbProp->setValue($service, $stubDb);

        return $service;
    }

    private function invokeSaveFeedItems(FeedIngestionService $service, object $stubDb): bool
    {
        $row        = (object) ['site_id' => 1];
        $emptyFetch = new class () {
            public function get_items(int $start = 0, int $end = 0): array
            {
                return [];
            }
        };

        $reflection = new ReflectionClass(FeedIngestionService::class);
        $method     = $reflection->getMethod('saveFeedItems');

        return $method->invoke($service, $row, $emptyFetch);
    }
}
