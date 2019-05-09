YubicoKey U2F
=============

Installation
------------
To enable this feature you need to

```
composer require yubico/u2flib-server:^1.0
```

Setup
-----

Enable it in your bootstrap.php file:

Config/bootstrap.php
```
Configure::write('U2f.enabled', true);
```

How does it work
----------------
When the user log-in, he is requested to insert and tap his registered yubico key,
if this is the first time he access he need to register the yubico key.

Please check the yubico site for more information about U2F
https://developers.yubico.com/U2F/

