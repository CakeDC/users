UserHelper
=============

The User Helper has some methods that may be needed if you want to improve your templates and add features to your app in an easy way.

Setup
---------------

Enable the Helper in `src/view/AppView.php`:
```php
class AppView extends View
{
    public function initialize()
    {
        parent::initialize();
        $this->loadHelper('CakeDC/Users.User');
    }
}
```

Social Login
-----------------

You can use the helper included with the plugin to create Facebook/Twitter buttons:

In templates
```php
echo $this->User->socialLogin($provider); //provider is 'facebook', 'twitter', etc
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

reCaptcha
-----------------

Handles the reCaptcha input display:

```php
$this->User->addReCaptchaScript();

$this->User->addReCaptcha();
```

Note reCaptcha script is added to script block when `addReCaptcha` method is called.
