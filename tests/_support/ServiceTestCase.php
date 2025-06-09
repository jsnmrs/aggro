<?php

namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * Base test case for service tests.
 */
abstract class ServiceTestCase extends CIUnitTestCase
{
    use DatabaseTestTrait;

    /**
     * Should the database be refreshed before each test?
     *
     * @var bool
     */
    protected $refresh = true;

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
     * @var \CodeIgniter\Database\BaseConnection
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
     * Helper method to insert test video data.
     *
     * @param array $data Video data
     *
     * @return bool
     */
    protected function insertTestVideo(array $data): bool
    {
        return $this->db->table('aggro_videos')->insert($data);
    }

    /**
     * Helper method to insert test channel data.
     *
     * @param array $data Channel data
     *
     * @return bool
     */
    protected function insertTestChannel(array $data): bool
    {
        return $this->db->table('aggro_sources')->insert($data);
    }

    /**
     * Helper method to count records in a table.
     *
     * @param string $table Table name
     * @param array  $where Optional where conditions
     *
     * @return int
     */
    protected function countRecords(string $table, array $where = []): int
    {
        $builder = $this->db->table($table);
        if (!empty($where)) {
            $builder->where($where);
        }

        return $builder->countAllResults();
    }
}