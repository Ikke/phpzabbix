<?php

use phpzabbix\PHPZabbix;
use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../vendor/autoload.php');

abstract class IntegrationTestCase extends TestCase
{
    protected $api;
    protected $settings;

    public function setUp(): void
    {
        $settings = [];
        if (file_exists(__DIR__.'/settings.php')) {
            $settings = require(__DIR__.'/settings.php');
        }

        $env = [
            'api_url' => 'ZABBIX_API_URL',
            'api_username' => 'ZABBIX_API_USERNAME',
            'api_password' => 'ZABBIX_API_PASSWORD',
            'api_token' => 'ZABBIX_API_TOKEN',
        ];
        foreach ($env as $key => $var) {
            if (getenv($var) !== false) {
                $settings[$key] = getenv($var);
            }
        }

        if (empty($settings['api_url'])) {
            $this->markTestSkipped('No Zabbix API url configured');
        }

        $this->api = PHPZabbix::withDefaultClient($settings['api_url']);
        $this->settings = $settings;
    }

    protected function versionAtLeast($version): bool
    {
        return version_compare($this->api->api_version(), $version, '>=');
    }
}
