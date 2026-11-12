<?php

require_once(__DIR__.'/IntegrationTestCase.php');

class ApiTest extends IntegrationTestCase
{
    public function testApiVersion()
    {
        $this->assertRegExp('/^\d+\.\d+\.\d+/', $this->api->api_version());
    }

    public function testUnknownMethodThrowsErrorException()
    {
        $this->api->login($this->settings['api_username'], $this->settings['api_password']);

        try {
            $this->api->foo->bar();
            $this->fail('Expected an ErrorException');
        } catch (\phpzabbix\JSONRPC\ErrorException $e) {
            $this->assertNotEmpty($e->data);
        }
    }

    public function testParamsArePassedThrough()
    {
        $this->api->login($this->settings['api_username'], $this->settings['api_password']);

        $name = uniqid('phpzabbix-test-');
        $created = $this->api->hostgroup->create(['name' => $name]);
        $groupid = current($created->groupids);

        try {
            $groups = $this->api->hostgroup->get([
                'output' => ['groupid', 'name'],
                'filter' => ['name' => [$name]],
            ]);

            $this->assertCount(1, $groups);
            $this->assertEquals($groupid, $groups[0]->groupid);
            $this->assertEquals($name, $groups[0]->name);
        } finally {
            $this->api->hostgroup->delete([$groupid]);
        }
    }
}
