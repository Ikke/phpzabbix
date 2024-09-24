<?php

namespace phpzabbix\test;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use phpzabbix\PHPZabbix;

require_once(__DIR__.'/../vendor/autoload.php');

class AuthorizationTest extends TestCase
{
    public function setUp(): void {
        error_reporting(E_ALL);
    }

    public function testAuthorizationPost640()
    {
        $container = [];
        $history = Middleware::history($container);

        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{"jsonrpc": "2.0", "id": 0, "result": null}'),
        ]);

        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $client = new Client([
            'handler' => $stack,
        ]);

        $api = new PHPZabbix($client, 'http://mock');
        $api->api_version = '6.4.0';
        $api->authToken = "abcdef123456";

        $resp = $api->host->get(['limit' => 1]);

        $this->assertCount(1, $container, "expected transaction to contain 1 request");
        $request = $container[0]['request'];

        $this->assertTrue(
            $request->hasHeader('Authorization'),
            'With zabbix >= 6.4.0, expected the client to send an authorization header'
        );

        $this->assertEquals("Bearer abcdef123456", $request->getHeader('authorization')[0]);
    }

    public function testAuthorizationPre640()
    {
        $container = [];
        $history = Middleware::history($container);

        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                '{"jsonrpc": "2.0", "id": 0, "result": null}'
            ),
        ]);

        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $client = new Client([
            'handler' => $stack,
        ]);

        $api = new PHPZabbix($client, 'http://mock');
        $api->api_version = '6.2.0';
        $api->authToken = "abcdef123456";

        $resp = $api->host->get(['limit' => 1]);

        $this->assertCount(1, $container, "expected transaction to contain 1 request");
        $request = $container[0]['request'];

        $apiRequest = json_decode($request->getBody());

        $this->assertObjectHasAttribute(
            'auth',
            $apiRequest,
            'With zabbix < 6.4.0, expected jsonrpc request to contain auth property'
        );
        $this->assertEquals("abcdef123456", $apiRequest->auth);
    }
}
