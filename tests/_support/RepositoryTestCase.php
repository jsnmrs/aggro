<?php

namespace Tests\Support;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * Base test case for repository tests.
 */
abstract class RepositoryTestCase extends CIUnitTestCase
{
    use DatabaseTestTrait;

    /**
     * Should the database be refreshed before each test?
     *
     * @var bool
     */
    protected $refresh = true;

    /**
     * Should run migration before tests?
     *
     * @var bool
     */
    protected $migrate = true;

    /**
     * The seed file(s) used for all tests within this test case.
     * Should be fully-namespaced class names, like [Namespace\]ClassName::class
     *
     * @var array
     */
    protected $seed = [];

    /**
     * The path to the fixtures directory.
     *
     * @var string
     */
    protected $basePath = 'tests/_support/Database/';

    /**
     * The namespace(s) to help us find the migration classes.
     *
     * @var array|string|null
     */
    protected $namespace = 'Tests\Support';

    /**
     * Database connection for tests.
     *
     * @var BaseConnection
     */
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::connect('tests');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Helper method to create a test video record.
     *
     * @param array $overrides Optional data to override defaults
     */
    protected function createTestVideo(array $overrides = []): array
    {
        $defaults = [
            'video_id'              => 'test_' . uniqid(),
            'aggro_date_added'      => date('Y-m-d H:i:s'),
            'aggro_date_updated'    => date('Y-m-d H:i:s'),
            'video_date_uploaded'   => date('Y-m-d H:i:s'),
            'flag_archive'          => 0,
            'flag_bad'              => 0,
            'video_plays'           => 100,
            'video_title'           => 'Test Video Title',
            'video_thumbnail_url'   => 'https://example.com/thumb.jpg',
            'video_width'           => 1920,
            'video_height'          => 1080,
            'video_aspect_ratio'    => '16:9',
            'video_duration'        => 300,
            'video_source_id'       => 'test_source',
            'video_source_username' => 'testuser',
            'video_source_url'      => 'https://example.com/video',
            'video_type'            => 'youtube',
        ];

        return array_merge($defaults, $overrides);
    }

    /**
     * Helper method to create a test channel record.
     *
     * @param array $overrides Optional data to override defaults
     */
    protected function createTestChannel(array $overrides = []): array
    {
        $defaults = [
            'source_slug'         => 'test_' . uniqid(),
            'source_type'         => 'youtube',
            'source_name'         => 'Test Channel',
            'source_url'          => 'https://example.com/channel',
            'source_date_added'   => date('Y-m-d H:i:s'),
            'source_date_updated' => date('Y-m-d H:i:s', strtotime('-1 hour')),
        ];

        return array_merge($defaults, $overrides);
    }
}
