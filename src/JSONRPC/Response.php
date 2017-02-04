<?php

namespace phpzabbix\JSONRPC;

class Response
{
    public $jsonrpc = "";
    public $result = null;
    public $id = 0;

    public function is_error()
    {
        return isset($this->error);
    }

    public static function from_json($response)
    {
        $resp = new Response();
        $data = json_decode($response);

        $resp->jsonrpc = $data->jsonrpc;
        $resp->id = $data->id;

        if(isset($data->error)) {
            $resp->error = Error::from_json_obj($data->error);
        } else {
            $resp->result = $data->result;
        }

        return $resp;
    }
}
