<?php

namespace phpzabbix;

use phpzabbix\JSONRPC\RequestCallbackInterface;

class RequestBuilder
{
    public $callback;
    public $objectName;

    function __construct(RequestCallbackInterface $callback)
    {
        $this->callback = $callback;
    }

    function __get($name)
    {
        $this->objectName = $name;
        return $this;
    }

    function __call($name, $arguments)
    {
        $methodname = sprintf("%s.%s", $this->objectName, $name);
        return $this->callback->call($methodname, current($arguments) ?: []);        
    }
}

