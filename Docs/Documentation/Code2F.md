Code2F (OTP authentication with phone / email)
=============

The plugin offers an easy way to integrate U2F in the users login flow
of your application.

How does it work
----------------
When the user log-in, he is requested to enter an OTP code sent to an email or phone. The code is requested based on the authentication checker selected.

Enabling
--------

First if you want to use OTP authentication with phone and you want to use builtin TwilioTransport, you need to require twilio/sdk using composer:

```
composer require twilio/sdk:@stable
```

Then add this in your config/users.php file:

```php
 'Code2f.enabled' => true,
```

Disabling
---------
You can disable it by adding this in your config/users.php file:

```php
 'Code2f.enabled' => false,
```

Configuration
-------------
To configure Code2f you need to set a checker class (see Checkers)
```php 
'Code2f' => [
    'enabled' => true,
    'checker' => \CakeDC\Auth\Authentication\DefaultCode2fTimeBasedAuthenticationChecker::class,
    'type' => \CakeDC\Auth\Authentication\Code2fAuthenticationCheckerInterface::CODE2F_TYPE_PHONE,
    'config' => 'sms',
    'message' => '{0} is your {1} verification code',
    'maxSeconds' => 300,
    'maxTries' => 3,
    'daysBeforeVerifyAgain' => 15
],
```
* `enabled`: Indicates if Code2f is enabled or not.
* `checker`: See Checkers below.
* `type`: Sets the recipient type. Can be `phone` or `email`.
* `config`: Email config to use. It does not matter if type is `phone` or `email` since we are using Email and Transports structure to deliver emails and SMS. (See Email Config)
* `message`: Message to be sent. It will be passed to translation function (__()). `{0}` is replaced with `code` generated and {1} is replaced with `App.name`.
* `maxSeconds`: Validity of the code.
* `maxTries`: Max tries before generating a new code.
* `daysBeforeVerifyAgain`: If using `Code2fTimeBasedAuthenticationChecker` this config sets the days before asking for OTP authentication again.

Email Config
------------
If you are using `email` type and you have an email config and transport configured you don't need to do anything else.

On the other hand if you are using `phone` type you will need a new email config and transport. For the example we use the built-in TwilioTransport that gives you SMS support out-of-the-box.

For `email` type it defaults to `default`. For `phone` it defaults to `sms`.

For Email Config you need to include custom `from` key, so it sets `from` parameter correctly. For Transport Config you need to set the `phonePattern` key to allow mailer to set the correct pattern for phones and avoid the exception thrown by default behavior.

```php 
'sms' => [
    'transport' => 'twilio',
    'from' => '+19876543210', //Complete phone number from your Twilio account
]
```

```php 
'twilio' => [
    'className' => \CakeDC\Users\Mailer\Transport\TwilioTransport::class,
    'phonePattern' => '/^\+[1-9]\d{1,14}$/i' //Phone pattern compatible with Twilio phone numbers
]
```

While `phonePattern` is required, `from` may not be since it depends on the actual transport. Remember you can always implement new transport to send SMS taking `TwilioTransport` as a reference.

Checkers 
-------------
We include three different checkers in our `cakedc/auth` plugin:

* \CakeDC\Auth\Authentication\DefaultCode2fAuthenticationChecker: Default authentication checker requires user to enter a new OTP code on each login.
* \CakeDC\Auth\Authentication\Code2fFingerprintAuthenticationChecker: Fingerprint authentication checker requires user to enter a new OTP code when fingerprint changes (fingerprint is calculated from user-agent header and ip)
* \CakeDC\Auth\Authentication\Code2fTimeBasedAuthenticationChecker: Time Based authentication checker requires user to enter a new OTP code after `daysBeforeVerifyAgain` days has passed since last validation.

