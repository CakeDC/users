Configuration
=============

Overriding the default configuration
-------------------------

For easier configuration, you can specify an array of config files to override the default plugin keys this way:

config/bootstrap.php
```
Configure::write('Users.config', ['users']);
Plugin::load('CakeDC/Users', ['routes' => true, 'bootstrap' => true]);
```

Then in your config/users.php
```
return [
    'Opauth.Strategy.Facebook.app_id' => 'YOUR APP ID',
    'Opauth.Strategy.Facebook.app_secret' => 'YOUR APP SECRET',
    'Opauth.Strategy.Twitter.key' => 'YOUR APP KEY',
    'Opauth.Strategy.Twitter.secret' => 'YOUR APP SECRET',
    //etc
];
```

Configuration for social login
---------------------

Create the facebook/twitter applications you want to use and setup the configuration like this:

config/bootstrap.php
```
Configure::write('Opauth.Strategy.Facebook.app_id', 'YOUR APP ID');
Configure::write('Opauth.Strategy.Facebook.app_secret', 'YOUR APP SECRET');

Configure::write('Opauth.Strategy.Twitter.key', 'YOUR APP KEY');
Configure::write('Opauth.Strategy.Twitter.secret', 'YOUR APP SECRET');
```

Or use the config override option when loading the plugin (see above)

Configuration options
---------------------

The plugin is configured via the Configure class. Check the `vendor/cakedc/users/config/users.php`
for a complete list of all the configuration keys.

Loading the UsersAuthComponent and using the right configuration values will setup the Users plugin,
the AuthComponent and the Opauth component for your application.

If you prefer to setup AuthComponent by yourself, you'll need to load AuthComponent before UsersAuthComponent
and set
```
Configure::write('Users.auth', false);
```

Interesting UsersAuthComponent options and defaults

NOTE: SOME keys were hidden in this doc page, please refer to `vendor/cakedc/users/config/users.php` for the complete list

```
    'Users' => [
        //Table used to manage users
        'table' => 'CakeDC/Users.Users',
        //configure Auth component
        'auth' => true,
        'Email' => [
            //determines if the user should include email
            'required' => true,
            //determines if registration workflow includes email validation
            'validate' => true,
        ],
        'Registration' => [
            //determines if the register is enabled
            'active' => true,
            //determines if the reCaptcha is enabled for registration
            'reCaptcha' => true,
        ],
        'Tos' => [
            //determines if the user should include tos accepted
            'required' => true,
        ],
        'Social' => [
            //enable social login
            'login' => true,
        ],
        //Avatar placeholder
        'Avatar' => ['placeholder' => 'CakeDC/Users.avatar_placeholder.png'],
        'RememberMe' => [
            //configure Remember Me component
            'active' => true,
        ],
    ],
//default configuration used to auto-load the Auth Component, override to change the way Auth works
    'Auth' => [
        'authenticate' => [
            'all' => [
                'scope' => ['active' => 1]
            ],
            'CakeDC/Users.RememberMe',
            'Form',
        ],
        'authorize' => [
            'CakeDC/Users.Superuser',
            'CakeDC/Users.SimpleRbac',
        ],
    ],
];

```

Default Authenticate and Authorize Objects used
------------------------

Using the UsersAuthComponent default initialization, the component will load the following objects into AuthComponent:
* Authenticate
  * 'Form'
  * 'Social' check [SocialAuthenticate](SocialAuthenticate.md) for configuration options
  * 'RememberMe' check [SocialAuthenticate](RememberMeAuthenticate.md) for configuration options
* Authorize
  * 'Users.Superuser' check [SuperuserAuthorize](SuperuserAuthorize.md) for configuration options
  * 'Users.SimpleRbac' check [SimpleRbacAuthorize](SimpleRbacAuthorize.md) for configuration options

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