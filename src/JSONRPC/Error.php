<?php

namespace phpzabbix\JSONRPC;

class Error
{
    public $code = 0;
    public $message;
    public $data = null;

    public static function from_json_obj($data)
    {
        $err = new Error();
        $err->code = $data->code;
        $err->message = $data->message;
        $err->data = $data->data;

        return $err;
    }

}
