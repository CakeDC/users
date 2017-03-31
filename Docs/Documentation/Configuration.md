Configuration
=============

Overriding the default configuration
-------------------------

For easier configuration, you can specify an array of config files to override the default plugin keys this way:

config/bootstrap.php
```
Configure::write('Users.config', ['users']);
Plugin::load('CakeDC/Users', ['routes' => true, 'bootstrap' => true]);
Configure::write('Users.Social.login', true); //to enable social login
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

config/bootstrap.php
```
Configure::write('OAuth.providers.facebook.options.clientId', 'YOUR APP ID');
Configure::write('OAuth.providers.facebook.options.clientSecret', 'YOUR APP SECRET');

Configure::write('OAuth.providers.twitter.options.clientId', 'YOUR APP ID');
Configure::write('OAuth.providers.twitter.options.clientSecret', 'YOUR APP SECRET');
```

Or use the config override option when loading the plugin (see above)

Configuration for reCaptcha
---------------------
```
Configure::write('Users.reCaptcha.key', 'YOUR RECAPTCHA KEY');
Configure::write('Users.reCaptcha.secret', 'YOUR RECAPTCHA SECRET');
Configure::write('Users.reCaptcha.registration', true); //enable on registration
Configure::write('Users.reCaptcha.login', true); //enable on login
```


Configuration options
---------------------

The plugin is configured via the Configure class. Check the `vendor/cakedc/users/config/users.php`
for a complete list of all the configuration keys.

Loading the UsersAuthComponent and using the right configuration values will setup the Users plugin,
the AuthComponent and the OAuth component for your application.

If you prefer to setup AuthComponent by yourself, you'll need to load AuthComponent before UsersAuthComponent
and set
```
Configure::write('Users.auth', false);
```

Interesting UsersAuthComponent options and defaults

NOTE: SOME keys were hidden in this doc page, please refer to `vendor/cakedc/users/config/users.php` for the complete list

```
    'Users' => [
        // Table used to manage users
        'table' => 'CakeDC/Users.Users',
        // Controller used to manage users plugin features & actions
        'controller' => 'CakeDC/Users.Users',
        // configure Auth component
        'auth' => true,
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
    ],
// default configuration used to auto-load the Auth Component, override to change the way Auth works
    'Auth' => [
        'authenticate' => [
            'all' => [
                'finder' => 'active',
            ],
            'CakeDC/Users.RememberMe',
            'Form',
        ],
        'authorize' => [
            'CakeDC/Auth.Superuser',
            'CakeDC/Auth.SimpleRbac',
        ],
    ],
];

```

Default Authenticate and Authorize Objects used
------------------------

Using the UsersAuthComponent default initialization, the component will load the following objects into AuthComponent:
* Authenticate
  * 'Form'
  * 'CakeDC/Users.Social' check [SocialAuthenticate](SocialAuthenticate.md) for configuration options
  * 'CakeDC/Auth.RememberMe' check [RememberMeAuthenticate](https://github.com/CakeDC/auth/blob/master/src/RememberMeAuthenticate.php) for configuration options
* Authorize
  * 'CakeDC/Auth.Superuser' check [SuperuserAuthorize](https://github.com/CakeDC/auth/blob/master/Docs/Documentation/SuperuserAuthorize.md) for configuration options
  * 'CakeDC/Auth.SimpleRbac' check [SimpleRbacAuthorize](https://github.com/CakeDC/auth/blob/master/Docs/Documentation/SimpleRbacAuthorize.md) for configuration options
  * 'CakeDC/Auth.ApiKey' check [ApiKeyAuthenticate](https://github.com/CakeDC/auth/blob/master/Docs/Documentation/ApiKeyAuthenticate.md) for configuration options

## Using the user's email to login

You need to configure 2 things:
* Change the Auth.authenticate.Form.fields configuration to let AuthComponent use the email instead of the username for user identify. Add this line to your bootstrap.php file, after CakeDC/Users Plugin is loaded

```php
Configure::write('Auth.authenticate.Form.fields.username', 'email');
```

* Override the login.ctp template to change the Form->control to "email". Add (or copy from the https://github.com/CakeDC/users/blob/master/src/Template/Users/login.ctp) the file login.ctp to path /src/Template/Plugin/CakeDC/Users/Users/login.ctp and ensure it has the following content

```php
        // ... inside the Form
        <?= $this->Form->control('email', ['required' => true]) ?>
        <?= $this->Form->control('password', ['required' => true]) ?>
        // ... rest of your login.ctp code
```




Email Templates
---------------

To modify the templates as needed copy them to your application

```
cp -r vendor/cakedc/users/src/Template/Email/* src/Template/Plugin/CakeDC/Users/Email/
```

Then customize the email templates as you need under the src/Template/Plugin/CakeDC/Users/Email/ directory

Plugin Templates
---------------

Similar to Email Templates customization, follow the CakePHP conventions to put your new templates under
src/Template/Plugin/CakeDC/Users/[Controller]/[view].ctp

Check http://book.cakephp.org/3.0/en/plugins.html#overriding-plugin-templates-from-inside-your-application

Flash Messages
---------------

To modify the flash messages, use the standard PO file provided by the plugin and customize the messages
Check http://book.cakephp.org/3.0/en/core-libraries/internationalization-and-localization.html#setting-up-translations
for more details about how the PO files should be managed in your application.

We've included an updated POT file with all the `Users` domain keys for your customization.
