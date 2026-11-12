<?php

namespace phpzabbix\JSONRPC;

class Request implements \JsonSerializable
{
    public $jsonrpc = '2.0';
    public $id = null;
    public $params = [];
    public $method = null;
    // Deprecated: only sent to Zabbix < 6.4.0, newer versions reject it
    public $auth = null;

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $data = get_object_vars($this);
        if ($this->auth === null) {
            unset($data['auth']);
        }

        return $data;
    }
}
