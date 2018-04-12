Google Two Factor Authenticator
===============================

Installation
------------
To enable this feature you need to

```
composer require robthree/twofactorauth
```

Setup
-----

Enable google authenticator in your bootstrap.php file:

Config/bootstrap.php
```
Configure::write('Users.GoogleAuthenticator.login', true);
```

How does it work
----------------
When the user log-in, he is requested (image 1) to inform the current validation
code for your site in Google Authentation app (image 2), if this is the first 
time he access he need to add your site to Google Authentation by reading
the QR code shown (image 1).

1) Validation code page

<img src="GoogleAuthenticator/FirstLogin.png?raw=true" width="300"/>

2) Google Authentation app

<img src="GoogleAuthenticator/App.png?raw=true" width="300"/>

