Installation
============

Composer
------

```
composer require cakedc/users
```

If you want to use social login features...

```
composer require league/oauth2-facebook:@stable
composer require league/oauth2-instagram:@stable
composer require league/oauth2-google:@stable
composer require league/oauth2-linkedin:@stable
composer require league/oauth1-client:@stable
```

NOTE: you'll need to enable social login if you want to use it, social
login is disabled by default. Check the [Configuration](Configuration.md#configuration-for-social-login) page for more details.

```
Configure::write('Users.Social.login', true); //to enable social login
```

If you want to use reCaptcha features...

```
composer require google/recaptcha:@stable
```

NOTE: you'll need to configure the reCaptcha key and secret, check the [Configuration](Configuration.md)
page for more details.

If you want to use Google Authenticator features...

```
composer require robthree/twofactorauth:"^1.5.2"
```

NOTE: you'll need to enable `OneTimePasswordAuthenticator.login`

```
Configure::write('OneTimePasswordAuthenticator.login', true);
```

Load the Plugin
-----------

Ensure the Users Plugin is loaded in your src/Application.php file

```
    /**
     * {@inheritdoc}
     */
    public function bootstrap()
    {
        parent::bootstrap();

        $this->addPlugin(\CakeDC\Users\Plugin::class);
        // Uncomment the line below to load your custom users.php config file
        //Configure::write('Users.config', ['users']);
    }
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

You can create the first user, the super user by issuing the following command

```
bin/cake users addSuperuser
```

Customization
----------

Application::bootstrap
```
$this->addPlugin(\CakeDC\Users\Plugin::class);
Configure::write('Users.config', ['users']);
Configure::write('Users.Social.login', true); //to enable social login
```

If you want to use social login, then in your config/users.php
```
return [
    'OAuth.providers.facebook.options.clientId' => 'YOUR APP ID',
    'OAuth.providers.facebook.options.clientSecret' => 'YOUR APP SECRET',
    'OAuth.providers.twitter.options.clientId' => 'YOUR APP ID',
    'OAuth.providers.twitter.options.clientSecret' => 'YOUR APP SECRET',
    //etc
];
```
IMPORTANT: Remember you'll need to configure your social login application **callback url** to use the provider specific endpoint, for example: 
* Facebook App Callback URL --> `http://yourdomain.com/auth/facebook`
* Twitter App Callback URL --> `http://yourdomain.com/auth/twitter`
* Google App Callback URL --> `http://yourdomain.com/auth/google`
* etc.

Note: using social authentication is not required.

For more details, check the Configuration doc page
