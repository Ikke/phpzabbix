<?php

use phpzabbix\PHPZabbix;
use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../vendor/autoload.php');

class AuthorizationTest extends TestCase
{
    protected $api;
    protected $settings;

    public function setUp()
    {
        $settings = require('settings.php');
        $this->api = PHPZabbix::withDefaultClient($settings['api_url']);

        $this->settings = $settings;
    }

    public function testInvalidCredentialsThrowsException()
    {
        $this->expectException(\phpzabbix\Exception\InvalidCredentials::class);
        $this->api->login('incorrect', '**');
    }

    public function testExpiredAuthTokenThrowsException()
    {
        $this->expectException(\phpzabbix\Exception\NotAuthorized::class);
        $this->api->authToken = "0123456789abcdef0123456789abcdef";
        $this->api->host->get(['limit' => 1]);
    }

    public function testLoginWithValidCredentials()
    {
        $user = $this->settings['api_user'];
        $password = $this->settings['api_password'];

        $this->api->login($user, $password);
        $user = current($this->api->user->get(['filter' => ['alias' => $user]]));

        $this->assertNotEmpty($user);
    }
}

