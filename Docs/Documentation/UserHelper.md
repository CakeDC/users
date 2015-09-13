UserHelper
=============

The User Helper has some methods that may be needed if you want to improve your templates and add features to your app in an easy way.

Setup
---------------

The User Helper just need some Configure variables to function properly.

Social Login
-----------------

You can use the helper included with the plugin to create Facebook/Twitter buttons:

In templates
```php
$this->User->facebookLogin();

$this->User->twitterLogin();
```

We recommend the use of [Bootstrap Social](http://lipis.github.io/bootstrap-social/) in order to automatically apply styles to buttons. Anyway you can always add your own style to the buttons.

Logout link
-----------------

It allows to add a logout link anywhere in the app.

```php
$this->User->logout();
```

RBAC link
-----------------

This function validates if you have access to a link and it displays it based on that.

```php
$this->User->link();
```

Welcome and profile link
-----------------

It displays a welcome message for the user including the name and a link to the profile page

```php
$this->User->welcome();
```

reCAPTCHA
-----------------

If you have configured reCAPTCHA for registration and have the proper key/secret configured then you will see the reCAPTCHA in registration page automatically.

You could also use it in another templates with the following methods:

```php
$this->User->addReCaptchaScript();

$this->User->addReCaptcha();
```

Note that the script is added automatically if the feature is enabled in config.