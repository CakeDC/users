Configuration
=============

Overriding the default configuration
-------------------------

For easier configuration, you can specify an array of config files to override the default plugin keys this way:

Make sure you loaded the plugin and is using a custom config/users.php file at Application::bootstrap
```
// The following configuration setting must be set before loading the Users plugin
$this->addPlugin(\CakeDC\Users\Plugin::class);
Configure::write('Users.config', ['users']);
```

Configuration for social login
---------------------

Create the facebook, twitter, etc applications you want to use and setup the configuration like this:
In this example, we are using 2 providers: facebook and twitter. Note you'll need to add the providers to
your composer.json file.

```
$ composer require league/oauth2-facebook:@stable
$ composer require league/oauth1-client:@stable
```

NOTE: twitter uses league/oauth1-client package

And update your config/users.php file:

```php
'Users.Social.login' => true,
'OAuth.providers.facebook.options.clientId' => 'YOUR APP ID',
'OAuth.providers.facebook.options.clientSecret' => 'YOUR APP SECRET',
'OAuth.providers.twitter.options.clientId' => 'YOUR APP ID',
'OAuth.providers.twitter.options.clientSecret' => 'YOUR APP SECRET',
```

Or use the config override option when loading the plugin (see above)

Additionally you will see you can configure two more keys for each provider:

* linkSocialUri (default: /link-social/**provider**),
* callbackLinkSocialUri(default: /callback-link-social/**provider**)

Those keys are needed to link an existing user account to a third-party account. **Remember to add the callback to your thrid-party app**

Configuration for reCaptcha
---------------------
To enable reCaptcha you need to register your site at google reCaptcha console
and add this to your config/users.php file:

```php
'Users.reCaptcha.key' => 'YOUR RECAPTCHA KEY',
'Users.reCaptcha.secret' => 'YOUR RECAPTCHA SECRET',
'Users.reCaptcha.registration' => true, //enable on registration
'Users.reCaptcha.login' => true, //enable on login
```


Configuration options
---------------------

The plugin is configured via the Configure class. Check the `vendor/cakedc/users/config/users.php`
for a complete list of all the configuration keys.

Loading the plugin and using the right configuration values will setup the Users plugin,
with authentication service, authorization service, and the OAuth components for your application.

This plugin uses by default the new [cakephp/authentication](https://github.com/cakephp/authentication)
and [cakephp/authorization](https://github.com/cakephp/authorization) plugins we suggest you to take a look
into their documentation for more information.

Most authentication/authorization configuration is defined at 'Auth' key, for example
if you don't want the plugin to autoload the authorization service, you could add this
to your config/users.php file:

```
'Auth.Authorization.enable' => false,
```

Interesting Users options and defaults

NOTE: SOME keys were hidden in this doc page, please refer to `vendor/cakedc/users/config/users.php` for the complete list

```
    'Users' => [
        // Table used to manage users
        'table' => 'CakeDC/Users.Users',
        // Controller used to manage users plugin features & actions
        'controller' => 'CakeDC/Users.Users',
        'Email' => [
            // determines if the user should include email
            'required' => true,
            // determines if registration workflow includes email validation
            'validate' => true,
        ],
        'Registration' => [
            // determines if the register is enabled
            'active' => true,
            // determines if the reCaptcha is enabled for registration
            'reCaptcha' => true,
            //ensure user is active (confirmed email) to reset his password
            'ensureActive' => false,
            // default role name used in registration
            'defaultRole' => 'user',
        ],
        'Tos' => [
            // determines if the user should include tos accepted
            'required' => true,
        ],
        'Social' => [
            // enable social login
            'login' => false,
        ],
        // Avatar placeholder
        'Avatar' => ['placeholder' => 'CakeDC/Users.avatar_placeholder.png'],
        'RememberMe' => [
            // configure Remember Me component
            'active' => true,
        ],
        'Superuser' => ['allowedToChangePasswords' => false], // able to reset any users password
    ],
    //Default authentication/authorization setup
    'Auth' => [
        'Authentication' => [
            'serviceLoader' => \CakeDC\Users\Loader\AuthenticationServiceLoader::class
        ],
        'AuthenticationComponent' => [...],
        'Authenticators' => [...],
        'Identifiers' => [...],
        "Authorization" => [
            'enable' => true,
            'serviceLoader' => \CakeDC\Users\Loader\AuthorizationServiceLoader::class
        ],
        'AuthorizationMiddleware' => [...],
        'AuthorizationComponent' => [...],
        'SocialLoginFailure' => [...],
        'FormLoginFailure' => [...],
        'RbacPolicy' => [
            'adapter' => [
                'role_field' => 'group_name',
                ...
            ]
        ]
    ],
    'SocialAuthMiddleware' => [...],
    'OAuth' => [...]
];

```

Authentication and Authorization
--------------------------------

This plugin uses the two new plugins cakephp/authentication and cakephp/authorization instead of
CakePHP Authentication component, but don't worry, the default configuration should be enough for your
projects. We tried to allow you to start quickly without the need to configure a lot of thing and also
allow you to configure as much as possible.

To learn more about it please check the configurations for [Authentication](Authentication.md) and [Authorization](Authorization.md)

## Using the user's email to login

You need to configure 2 things (version 9.0.4):

* Change the Password identifier fields and the Authenticator for Forms
configuration to let it use the email instead of the username for
user identify. Add this to your config/users.php:

```php
'Auth.Identifiers.Password.fields.username' => 'email',
'Auth.Authenticators.Form.fields.username' => 'email',
```

* Override the login.php template to change the Form->control to "email".
Add (or copy from the [/templates/Users/login.php](../../templates/Users/login.php)) the file login.php to path /templates/plugin/CakeDC/Users/Users/login.php
and ensure it has the following content

```php
        // ... inside the Form
        <?= $this->Form->control('email', ['required' => true]) ?>
        <?= $this->Form->control('password', ['required' => true]) ?>
        // ... rest of your login.php code
```




Email Templates
---------------

To modify the templates as needed copy them to your application

```
cp -r vendor/cakedc/users/templates/email/ templates/plugin/CakeDC/Users/email/
```

Then customize the email templates as you need under the templates/Plugin/CakeDC/Users/email/ directory

Plugin Templates
---------------

Similar to Email Templates customization, follow the CakePHP conventions to put your new templates under
templates/plugin/CakeDC/Users/[Controller]/[view].php

Check https://book.cakephp.org/4/en/plugins.html#overriding-plugin-templates-from-inside-your-application

Flash Messages
---------------

To modify the flash messages, use the standard PO file provided by the plugin and customize the messages
Check https://book.cakephp.org/4/en/core-libraries/internationalization-and-localization.html#setting-up-translations
for more details about how the PO files should be managed in your application.

We've included an updated POT file with all the `Users` domain keys for your customization.

Password Hasher customization
-----------------------------

Override the `Auth.Identifiers.Password` key in configuration adding a `passwordHasher` key https://book.cakephp.org/authentication/2/en/password-hashers.html#upgrading-hashing-algorithms

For example:

```php
    'Auth.Identifiers' => [
        'Password' => [
            'className' => 'Authentication.Password',
            'fields' => [
                'username' => ['username', 'email'],
                'password' => 'password',
            ],
            'resolver' => [
                'className' => 'Authentication.Orm',
                'finder' => 'active',
            ],
            'passwordHasher' => [
                'className' => 'Authentication.Fallback',
                'hashers' => [
                    'Authentication.Default',
                    [
                        'className' => 'Authentication.Legacy',
                        'hashType' => 'md5',
                        'salt' => false, // turn off default usage of salt
                    ],
                ],
            ],
        ],
    ],
```
