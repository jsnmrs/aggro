<?php

use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Libraries\ConfigReader;

/**
 * @internal
 */
final class HealthTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testIsDefinedAppPath()
    {
        $test = defined('APPPATH');

        $this->assertTrue($test);
    }

    public function testBaseUrlHasBeenSet()
    {
        $env = $config = false;

        // First check in .env
        if (is_file(HOMEPATH . '.env')) {
            $env = (bool) preg_grep("/^app\\.baseURL = './", file(HOMEPATH . '.env'));
        }

        // Then check the actual config file
        $reader = new ConfigReader();
        $config = ! empty($reader->baseURL);

        $this->assertTrue($env || $config);
    }
}
