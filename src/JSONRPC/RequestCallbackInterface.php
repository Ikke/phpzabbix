<?php

namespace phpzabbix\JSONRPC;

interface RequestCallbackInterface
{
    public function call($method, array $params = []);
}
