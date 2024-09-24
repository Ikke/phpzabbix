<?php

namespace phpzabbix;

use \Curl\Curl;
use phpzabbix\JSONRPC\Request;
use phpzabbix\JSONRPC\Response;
use phpzabbix\JSONRPC\RequestCallbackInterface;
use phpzabbix\JSONRPC\ErrorException;
use phpzabbix\Exception\NotAuthorized;
use phpzabbix\Exception\InvalidCredentials;

use \GuzzleHttp\ClientInterface;

class PHPZabbix implements RequestCallbackInterface
{
    public $client;
    public $apiUrl;

    public $authToken;
    public $currentId=1;

    public $api_version=null;

    public $no_auth_methods = [
        'user.login',
        'apiinfo.version'
    ];

    public function __construct(ClientInterface $client, $apiUrl)
    {
        $this->client = $client;
        $this->apiUrl = $apiUrl;
    }

    public static function withDefaultClient($apiUrl) {
        $client = new \GuzzleHttp\Client();
        return new PHPZabbix($client, $apiUrl);
    }


    public function call($method, array $params = [])
    {
        $headers = ['Content-Type' => 'application/json-rpc'];

        $use_auth_header = false;
        if($this->method_requires_authentication($method)) {
            $use_auth_header = version_compare($this->api_version(), '6.4.0', '>=');
            if($use_auth_header) {
                $headers['Authorization'] = sprintf('Bearer %s', $this->authToken);
            }
        }

        $req = $this->create_jsonrpc_request($method, $params, !$use_auth_header);
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
        $usernameField = 'username';
        if(version_compare($this->api_version(), '5.4.0', '<')) {
            $usernameField = 'user';
        }

        $this->authToken = $this->user->login(
            [$usernameField => $username, 'password' => $password]
        );
    }

    public function api_version()
    {
        if($this->api_version == null) {
            $this->api_version = $this->apiinfo->version();
        }

        return $this->api_version;
    }

    public function create_jsonrpc_request($method, $params = [], $use_auth_param=true)
    {
        $req = new Request();
        $req->id = $this->currentId++;
        $req->method = $method;
        $req->params = $params;

        if($use_auth_param && $this->method_requires_authentication($method)) {
            $req->auth = $this->authToken;
        }

        return $req;
    }

    public function method_requires_authentication($method): bool {
        return !in_array($method, $this->no_auth_methods);
    }

    public function raise_for_jsonrpc_error(Response $response)
    {
        if($response->is_error()) {
            if (preg_match('/^Session terminated/', $response->error->data)) {
                throw new NotAuthorized();
            } elseif (preg_match('/^(Login|Incorrect user) name or password/', $response->error->data)) {
                throw new InvalidCredentials();
            } else {
                throw new ErrorException(
                    $response->error->message,
                    $response->error->code,
                    $response->error->data
                );
            }
        }
    }

    public function __get($name)
    {
        return (new RequestBuilder($this))->$name;
    }
}
