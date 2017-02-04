<?php

namespace phpzabbix\JSONRPC;

class Request
{
    public $jsonrpc = '2.0';
    public $id = null;
    public $params = [];
    public $method = null;
}
