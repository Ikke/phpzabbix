# PHPZabbix

Zabbix PHP API library based on [pyzabbix][0].

## Example

    $api = new phpzabbix\PHPZabbix('http://example.com/zabbix/api_jsonrpc.php');
    $api->login('username', 'password');

    $hosts = $api->host->get(['output' => ['hostid', 'name']]);

## Documentation

The PHPZabbix class does not know every API call, but just translates
`$api->obj->method()` to 'obj.method'. The method call takes an array which
is passed as params.

Refer to the [Zabbix API][1] for more information about the Zabbix API
itself.

## License

This code is distributed under the [GPLv3][2] license.

[0]:https://github.com/lukecyca/pyzabbix
[1]:https://www.zabbix.com/documentation/3.0/manual/api
[2]:LICENSE
