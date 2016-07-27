ApiKeyAuthenticate
=============

Setup
---------------

ApiKeyAuthenticate default configuration is
```php
    protected $_defaultConfig = [
        //type, can be either querystring or header
        'type' => self::TYPE_QUERYSTRING,
        //name to retrieve the api key value from
        'name' => 'api_key',
        //db field where the key is stored
        'field' => 'api_token',
        //require SSL to pass the token. You should always require SSL to use tokens for Auth
        'require_ssl' => true,
    ];
```

We are using query strings for passing the api_key token. And we require SSL by default.
Note you can override these options using

```php
$config['Auth']['authenticate']['CakeDC/Users.ApiKey'] = [
    'type' => 'header',
    ];
```

In order to allow stateless authentication, enable these configuration:

```php
    $this->Auth->config('storage', 'Memory');
    $this->Auth->config('unauthorizedRedirect', 'false');
    $this->Auth->config('checkAuthIn', 'Controller.initialize');
    $this->Auth->config('loginAction', false);
```