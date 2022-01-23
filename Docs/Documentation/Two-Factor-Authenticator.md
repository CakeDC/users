Two Factor Authenticator
===============================
The plugin offers an easy way to integrate OTP Two-Factor authentication
in the users login flow of your application.


Installation Requirement
------------------------
Before you enable the feature you need to run

```
composer require robthree/twofactorauth
```

By default the feature is disabled.

Enabling
--------

First install robthree/twofactorauth using composer:

```
composer require robthree/twofactorauth
```

Then add this in your config/users.php file:

```php
 'OneTimePasswordAuthenticator.login' => true,
```

Disabling
---------
You can disable it by adding this in your config/users.php file:

```php
 'OneTimePasswordAuthenticator.login' => false,
```

How does it work
----------------
When the user log-in, he is requested (image 1) to inform the current validation
code for your site in Google Authentation app (image 2), if this is the first
time he access he need to add your site to Google Authentation by reading
the QR code shown (image 1).

1) Validation code page

<img src="OneTimePasswordAuthenticator/FirstLogin.png?raw=true" width="300"/>

2) Google Authentation app

<img src="OneTimePasswordAuthenticator/App.png?raw=true" width="300"/>

