<?php

namespace phpzabbix;

use \Curl\Curl;
use phpzabbix\JSONRPC\Request;
use phpzabbix\JSONRPC\Response;
use phpzabbix\JSONRPC\RequestCallbackInterface;
use phpzabbix\JSONRPC\ErrorException;

class PHPZabbix implements RequestCallbackInterface
{
    public $client;
    public $apiUrl;

    public $authToken;
    public $currentId=1;

    public $no_auth_methods = [
        'user.login',
        'apiinfo.version'
    ];

    public function __construct(ClientInterface $client, $apiUrl)
    {
        $this->client = new \GuzzleHttp\Client();
        $this->apiUrl = $apiUrl;
    }

    public function call($method, array $params = [])
    {
        $headers = ['Content-Type' => 'application/json-rpc'];

        $req = $this->create_jsonrpc_request($method, $params);
        $http_response = $this->client->post(
            $this->apiUrl, [
                'headers' => $headers,
                'body' => json_encode($req)
            ]
        );

        $response = Response::from_json($http_response->getBody());
        $this->raise_for_jsonrpc_error($response);

        return $response->result;
    }

    public function login($username, $password)
    {
        $this->authToken = $this->user->login(
            ['user' => $username, 'password' => $password]
        );
    }

    public function create_jsonrpc_request($method, $params = [])
    {
        $req = new Request();
        $req->id = $this->currentId++;
        $req->method = $method;
        $req->params = $params;

        if(!in_array($method, $this->no_auth_methods)) {
            $req->auth = $this->authToken;
        }

        return $req;
    }

    public function raise_for_jsonrpc_error(Response $response)
    {
        if($response->is_error()) {
            throw new ErrorException(
                $response->error->message,
                $response->error->code,
                $response->error->data
            );
        }
    }

    public function __get($name)
    {
        return (new RequestBuilder($this))->$name;
    }
}

