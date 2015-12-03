Installation
============

Composer
------

```
composer require cakedc/users:3.1.*
```

if you want to use social login features...

```
composer require Muffin/OAuth2:*
composer require league/oauth2-facebook:*
composer require league/oauth2-instagram:*
composer require league/oauth2-google:*
composer require league/oauth2-linkedin:*

```

Creating Required Tables
------------------------
If you want to use the Users tables to store your users and social accounts:

```
bin/cake migrations migrate -p CakeDC/Users
```

Note you don't need to use the provided tables, you could customize the table names, fields etc in your
application and then use the plugin configuration to use your own tables instead. Please refer to the [Extending the Plugin](Extending-the-Plugin.md)
section to check all the customization options

Load the Plugin
-----------

Ensure the Users Plugin is loaded in your config/bootstrap.php file

```
Plugin::load('CakeDC/Users', ['routes' => true, 'bootstrap' => true]);
```

Customization
----------

config/bootstrap.php
```
Configure::write('Users.config', ['users']);
Plugin::load('CakeDC/Users', ['routes' => true, 'bootstrap' => true]);
```

Then in your config/users.php
```
return [
    'OAuth.providers.facebook.options.clientId' => 'YOUR APP ID',
    'OAuth.providers.facebook.options.clientSecret' => 'YOUR APP SECRET',
    'OAuth.providers.instagram.options.clientId' => 'YOUR APP ID',
    'OAuth.providers.instagram.options.clientSecret' => 'YOUR APP SECRET',
    //etc
];

```

For more details, check the Configuration doc page

Load the UsersAuth Component
---------------------

Load the Component in your src/Controller/AppController.php, and use the passed Component configuration to customize the Users Plugin:

```
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Flash');
        $this->loadComponent('CakeDC/Users.UsersAuth');
    }
```
