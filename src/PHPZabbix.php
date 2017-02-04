<?php

namespace phpzabbix;

use \Curl\Curl;
use phpzabbix\JSONRPC\Request;
use phpzabbix\JSONRPC\Response;
use phpzabbix\JSONRPC\RequestCallbackInterface;
use phpzabbix\JSONRPC\ErrorException;
use phpzabbix\Exception\CurlException;
use phpzabbix\Exception\HTTPException;

class PHPZabbix implements RequestCallbackInterface
{
    public $apiUrl;
    public $authToken;
    public $currentId=1;

    public $no_auth_methods = ['user.login', 'apiinfo.version'];

    public function __construct($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }
    
    public function call($method, array $params = [])
    {
        $curl = new Curl();
        $curl->setHeader('Content-Type', 'application/json-rpc');

        $req = $this->create_request($method, $params);
        $curl->post($this->apiUrl, json_encode($req));

        $this->raise_for_http_error($curl);

        $response = Response::from_json($curl->response);
        $this->raise_for_jsonrpc_error($response);
        
        return $response->result;
    }

    public function login($username, $password)
    {
        $this->authToken = $this->user->login(
            ['user' => $username, 'password' => $password]
        );
    }

    public function create_request($method, $params = [])
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

    public function raise_for_http_error(Curl $curl)
    {
        if($curl->error) {
            throw new CurlException(
                $curl->curl_error_message,
                $curl->curl_error_code
            );
        }
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

