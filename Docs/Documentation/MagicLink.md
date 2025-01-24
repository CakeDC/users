Magic Link
===============================
The plugin offers an easy way to add one-click login capabilities (through a link sent to user email)


Installation Requirement
------------------------
There are no package requirements for using this feature. `Users.Email.required` setting must be set to true

By default the feature is enabled. The default configuration is:

```php
'OneTimeLogin' => [
    'enabled' => true,
    'tokenLifeTime' => 600,
    'DeliveryHandlers' => [
        'Email' => [
            'className' => \CakeDC\Users\Model\Behavior\OneTimeDelivery\EmailDelivery::class
        ],
    ],
],
```
* `tokenLifeTime`: 60 minutes by default. You can set how many seconds you want your token to be valid.
* `DelveryHandlers`: Email delivery is included but it can be easily extended implementing `\CakeDC\Users\Model\Behavior\OneTimeDelivery\DeliveryInterface` (i.e SmsDelivery, PushDelivery, etc)

Enabling
--------

The feature is enabled by default but you can disable it application-wide and enable via Middleware (or any other way) for specific situations using:

```php
Configure::write('OneTimeLogin.enabled', true),
```

Disabling
---------
You can disable it by adding this in your config/users.php file:

```php
 'OneTimeLogin.enabled' => false,
```

How does it work
----------------
When the user access the login page, there is a new button `Send me a login link`. On click, the user will be redirected to a page to enter his email address. Once it is submitted, the user will receive an email with the link to automatically login.

Two-factor authentication
----------------
The two-factor authentication is skipped by default for this feature since the user must actively click on a link sent to his email address.

If you want to enable it by adding this in your config/users.php file:

```php
'Auth.Authenticators.OneTimeToken.skipTwoFactorVerify' => false,
```

ReCaptcha
----------------
ReCaptcha will be added automatically to the request login link form if `Users.reCaptcha.login` is enabled. We strongly recommend having ReCaptcha enabled, because it's a public form that could be targeted by an attacker to send multiple requests.

