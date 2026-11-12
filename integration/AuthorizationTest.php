<?php

require_once(__DIR__.'/IntegrationTestCase.php');

class AuthorizationTest extends IntegrationTestCase
{
    protected function getUser($username)
    {
        $field = $this->versionAtLeast('5.4.0') ? 'username' : 'alias';
        return current($this->api->user->get(['filter' => [$field => $username]]));
    }

    public function testInvalidCredentialsThrowsException()
    {
        $this->expectException(\phpzabbix\Exception\InvalidCredentials::class);
        $this->api->login('incorrect', '**');
    }

    public function testInvalidAuthTokenThrowsException()
    {
        $this->expectException(\phpzabbix\Exception\NotAuthorized::class);
        $this->api->authToken = "0123456789abcdef0123456789abcdef";
        $this->api->host->get(['limit' => 1]);
    }

    public function testLoginWithValidCredentials()
    {
        $username = $this->settings['api_username'];
        $password = $this->settings['api_password'];

        $this->api->login($username, $password);

        $this->assertNotEmpty($this->getUser($username));
    }

    public function testApiToken()
    {
        if (!$this->versionAtLeast('5.4.0')) {
            $this->markTestSkipped('API tokens require Zabbix >= 5.4');
        }

        $this->api->authToken = $this->settings['api_token'];

        $this->assertNotEmpty($this->getUser($this->settings['api_username']));
    }

    public function testLogoutTerminatesSession()
    {
        $this->api->login($this->settings['api_username'], $this->settings['api_password']);
        $this->assertTrue($this->api->user->logout());

        $this->expectException(\phpzabbix\Exception\NotAuthorized::class);
        $this->api->host->get(['limit' => 1]);
    }

    public function testCheckAuthenticationWithoutAuth()
    {
        $username = $this->settings['api_username'];
        $this->api->login($username, $this->settings['api_password']);

        $user = $this->api->user->checkAuthentication(['sessionid' => $this->api->authToken]);

        $this->assertEquals($this->getUser($username)->userid, $user->userid);
    }
}
