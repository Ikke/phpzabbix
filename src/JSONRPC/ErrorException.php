<?php

namespace phpzabbix\JSONRPC;

class ErrorException extends \Exception
{
    public $data = null;

    public function __construct($message, $code, $data, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->data = $data;
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}: {$this->data}\n";
    }
}
