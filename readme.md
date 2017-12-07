# PHPZabbix

Zabbix PHP API library based on [pyzabbix][0].

## Example

    $api = phpzabbix\PHPZabbix::withDefaultClient('http://example.com/zabbix/api_jsonrpc.php');
    $api->login('username', 'password');

    $hosts = $api->host->get(['output' => ['hostid', 'name']]);

## Error handling

The following exceptions can be thrown:

* `phpzabbix\Exception\NotAuthorized` when trying to make an API call with an
  invalid or expired auth hash.

* `phpzabbix\Exception\InvalidCredentials` when trying to login with invalid
  credentials.

* `phpzabbix\JSONRPC\ErrorException` on other errors, such as invalid API calls

## Documentation

The PHPZabbix class does not know every API call, but just translates
`$api->obj->method()` to 'obj.method'. The method call takes an array which
is passed as params.

Refer to the [Zabbix API][1] for more information about the Zabbix API
itself.

## Compattibility

This library should work with Zabbix 3.0 and higher. Older versions should also
work, but that has not been verified.

## License

This code is distributed under the [GPLv3][2] license.

[0]:https://github.com/lukecyca/pyzabbix
[1]:https://www.zabbix.com/documentation/3.4/manual/api
[2]:LICENSE
