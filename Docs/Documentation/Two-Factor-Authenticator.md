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


Skipping 2FA Verification
-------------------------
You can conditionally skip the Two-Factor Authentication verify step for specific users 
or scenarios by listening to the `UsersPlugin::EVENT_2FA_SKIP_VERIFY` event.

If the event returns `true`, the verification is skipped, and the user is logged in immediately. 
The event receives the `user` data as a payload.

**Example: Skipping 2FA based on User Role**

In your `src/Application.php` or a dedicated Event Listener:

```php
use Cake\Event\EventInterface;
use CakeDC\Users\UsersPlugin;

// ...

$this->getEventManager()->on(UsersPlugin::EVENT_2FA_SKIP_VERIFY, function (EventInterface $event) {
    $user = $event->getData('user');

    // Check if the user has the 'admin' role
    if (!empty($user['role']) && $user['role'] === 'admin') {
        // Return true to skip the 2FA verification for this user role
        $event->setResult(true);
    }
});
```