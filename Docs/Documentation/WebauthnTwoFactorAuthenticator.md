Webauthn Two-Factor Authenticator
=================================

Enable
------
**This feature requires the use of SSL**

First install the library web-auth/webauthn-lib using composer:

```
composer require web-auth/webauthn-lib:^4.4
```

Then add this in your config/users.php file:

```php
    'Webauthn2fa' => [
        'enabled' => true,
        'appName' => 'MyApplicationName',
    ],
```
Make sure anybody has permissions to access webauthn actions, ex:

config/permissions.php
```
return [
    .........other permissions defined
        [
            'prefix' => false,
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => [
                'webauthn2fa',
                'webauthn2faRegister',
                'webauthn2faRegisterOptions',
                'webauthn2faAuthenticate',
                'webauthn2faAuthenticateOptions',
            ],
            'bypassAuth' => true,
        ],
```

Disable
-------
You can disable it by adding this in your config/users.php file:

```php
 'Webauthn2fa.enabled' => false,
```

How does it work
----------------
When the user logs in, he is required to use a secure device that
is compatible with web authentication API, one of them is a yubico key.
When logging in for the first time, the user will need to register the
device being used.

Links
-----
- PHP Library: https://webauthn-doc.spomky-labs.com/
- Webauthn specification: https://w3c.github.io/webauthn/
