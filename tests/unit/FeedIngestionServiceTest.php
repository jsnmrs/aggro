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
            'site_url'             => 'https://site1.example.org',
            'site_feed'            => 'https://site1.example.org/feed.xml',
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
            'site_url'             => 'https://site2.example.org',
            'site_feed'            => 'https://site2.example.org/feed.xml',
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

    public function testSaveFeedItemsLeavesUnchangedStoriesAlone()
    {
        $batch = [
            ['First story', 'https://example.com/first', '2026-10-01 10:00:00'],
            ['Second story', 'https://example.com/second', '2026-10-02 10:00:00'],
        ];

        $this->assertTrue($this->ingest($batch));
        $before = $this->storedStories();

        $this->assertTrue($this->ingest($batch));

        // Same rows, same ids: the repeat run wrote nothing
        $this->assertSame($before, $this->storedStories());
        $this->assertCount(2, $before);
    }

    public function testSaveFeedItemsInsertsOnlyNewStories()
    {
        $first = ['First story', 'https://example.com/first', '2026-10-01 10:00:00'];
        $new   = ['New story', 'https://example.com/new', '2026-10-03 10:00:00'];

        $this->ingest([$first]);
        $before = $this->storedStories();

        $this->assertTrue($this->ingest([$new, $first]));
        $after = $this->storedStories();

        $this->assertCount(2, $after);
        $this->assertSame($before['https://example.com/first'], $after['https://example.com/first']);
        $this->assertSame('New story', $after['https://example.com/new']['story_title']);
    }

    public function testSaveFeedItemsUpdatesChangedStoryInPlace()
    {
        $this->ingest([['Original title', 'https://example.com/first', '2026-10-01 10:00:00']]);
        $before = $this->storedStories()['https://example.com/first'];

        $this->assertTrue($this->ingest([['Corrected title', 'https://example.com/first', '2026-10-01 12:30:00']]));
        $after = $this->storedStories();

        $this->assertCount(1, $after);
        $this->assertSame($before['story_id'], $after['https://example.com/first']['story_id']);
        $this->assertSame('Corrected title', $after['https://example.com/first']['story_title']);
        $this->assertSame('2026-10-01 12:30:00', $after['https://example.com/first']['story_date']);
    }

    public function testSaveFeedItemsMovesSharedStoryToLatestSiteInPlace()
    {
        $story = ['Shared story', 'https://example.com/shared', '2026-10-01 10:00:00'];

        $this->ingest([$story], 1);
        $before = $this->storedStories()['https://example.com/shared'];

        // A second site whose feed carries the same permalink takes the story over
        $this->assertTrue($this->ingest([$story], 2));
        $after = $this->storedStories();

        $this->assertCount(1, $after);
        $this->assertSame($before['story_id'], $after['https://example.com/shared']['story_id']);
        $this->assertSame('2', (string) $after['https://example.com/shared']['site_id']);
    }

    public function testSaveFeedItemsMatchesPermalinkStoredAtColumnWidth()
    {
        // MySQL can cut a long permalink to the 255-character column width
        $long   = 'https://example.com/' . str_repeat('a', 300);
        $stored = substr($long, 0, 255);

        $this->db->table('news_featured')->insert([
            'site_id'         => 1,
            'story_title'     => 'Long link story',
            'story_permalink' => $stored,
            'story_hash'      => sha1($long),
            'story_date'      => '2026-10-01 10:00:00',
        ]);

        $this->assertTrue($this->ingest([['Long link story', $long, '2026-10-01 10:00:00']]));

        $this->assertSame(1, $this->countRecords('news_featured'));
    }

    public function testSaveFeedItemsStoresRepeatedPermalinkOnce()
    {
        $story = ['Repeated story', 'https://example.com/repeated', '2026-10-01 10:00:00'];

        $this->assertTrue($this->ingest([$story, $story]));

        $this->assertSame(1, $this->countRecords('news_featured'));
    }

    /**
     * Ingest a batch of stories through saveFeedItems().
     *
     * @param list<array{0: string, 1: string, 2: string}> $stories Title, permalink, and date for each story
     */
    private function ingest(array $stories, int $siteId = 1): bool
    {
        helper('aggro');

        $items = array_map(static fn (array $story): object => new class (...$story) {
            public function __construct(
                private readonly string $title,
                private readonly string $permalink,
                private readonly string $date,
            ) {
            }

            public function get_title(): string
            {
                return $this->title;
            }

            public function get_permalink(): string
            {
                return $this->permalink;
            }

            public function get_date(string $format): string
            {
                return $this->date;
            }
        }, $stories);

        $fetch = new class ($items) {
            public function __construct(private readonly array $items)
            {
            }

            public function get_items(int $start = 0, int $end = 0): array
            {
                return $this->items;
            }
        };

        $reflection = new ReflectionClass(FeedIngestionService::class);
        $method     = $reflection->getMethod('saveFeedItems');

        return $method->invoke($this->service, (object) ['site_id' => $siteId], $fetch);
    }

    /**
     * Get every stored story, keyed by permalink.
     *
     * @return array<string, array<string, mixed>>
     */
    private function storedStories(): array
    {
        $rows = $this->db->table('news_featured')->orderBy('story_id', 'ASC')->get()->getResultArray();

        return array_column($rows, null, 'story_permalink');
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
