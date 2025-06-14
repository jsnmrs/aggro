<?php

namespace Config;

use CodeIgniter\Database\Config;
use RuntimeException;

/**
 * Database Configuration
 */
class Database extends Config
{
    /**
     * The directory that holds the Migrations
     * and Seeds directories.
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Lets you choose which connection group to
     * use if no other is specified.
     */
    public string $defaultGroup = 'default';

    /**
     * The default database connection.
     *
     * IMPORTANT: In production, set database credentials via environment variables:
     * - database.default.username
     * - database.default.password
     * - database.default.database
     * - database.default.hostname (if not localhost)
     */
    public array $default = [
        'DSN'          => '',
        'hostname'     => 'localhost',
        'username'     => '',
        'password'     => '',
        'database'     => '',
        'DBDriver'     => 'MySQLi',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => ENVIRONMENT !== 'production',
        'charset'      => 'utf8mb4',
        'DBCollat'     => 'utf8mb4_unicode_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => 3306,
        'numberNative' => false,
    ];

    /**
     * This database connection is used when
     * running PHPUnit database tests.
     */
    public array $tests = [
        'DSN'         => '',
        'hostname'    => '127.0.0.1',
        'username'    => '',
        'password'    => '',
        'database'    => ':memory:',
        'DBDriver'    => 'SQLite3',
        'DBPrefix'    => 'tests_',  // Needed to ensure we're working correctly with prefixes live. DO NOT REMOVE FOR CI DEVS
        'pConnect'    => false,
        'DBDebug'     => true,
        'charset'     => 'utf8',
        'DBCollat'    => 'utf8_general_ci',
        'swapPre'     => '',
        'encrypt'     => false,
        'compress'    => false,
        'strictOn'    => false,
        'failover'    => [],
        'port'        => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
    ];

    public function __construct()
    {
        parent::__construct();

        // Ensure that we always set the database group to 'tests' if
        // we are currently running an automated test suite, so that
        // we don't overwrite live data on accident.
        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }

        // Validate production database configuration
        if (ENVIRONMENT === 'production') {
            $this->validateProductionConfig();
        }
    }

    /**
     * Validate production database configuration.
     *
     * @throws RuntimeException
     */
    private function validateProductionConfig(): void
    {
        $config = $this->default;

        // Check required database credentials
        if (empty($config['username'])) {
            throw new RuntimeException('Database username must be configured for production environment');
        }

        if (empty($config['password'])) {
            throw new RuntimeException('Database password must be configured for production environment');
        }

        if (empty($config['database'])) {
            throw new RuntimeException('Database name must be configured for production environment');
        }

        // Ensure debug is disabled in production
        if ($config['DBDebug'] === true) {
            throw new RuntimeException('Database debug mode must be disabled in production environment');
        }

        // Warn about security settings
        if ($config['charset'] !== 'utf8mb4') {
            log_message('warning', 'Database charset should be utf8mb4 for better Unicode support and security');
        }
    }
}
