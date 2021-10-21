YubicoKey U2F
=============

The plugin offers an easy way to integrate U2F in the users login flow
of your application.

Enabling
--------

First install yubico/u2flib-server using composer:

```
composer require yubico/u2flib-server:^1.0
```

Then add this in your config/users.php file:

```php
 'U2f.enabled' => true,
```

Disabling
---------
You can disable it by adding this in your config/users.php file:

```php
 'U2f.enabled' => false,
```

How does it work
----------------
When the user log-in, he is requested to insert and tap his registered yubico key,
if this is the first time he access he need to register the yubico key.

Please check the yubico site for more information about U2F
https://developers.yubico.com/U2F/

