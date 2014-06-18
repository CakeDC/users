Configuration
=============

Email Templates
---------------

To modify the templates as needed copy them to

```
/app/View/Plugin/Users/Emails/
```

Note that you will have to overwrite any view that is linking to the plugin like the email verification email.

Disable Slugs
-------------

If the Utils plugin is present the users model will auto attach and use the sluggable behavior.

To not create slugs for a new user records put this in your configuration: Configure::write('Users.disableSlugs', true);

Email configuration
-------------------

The plugin uses the $default email configuration (should be present in your Config/email.php file), but you can override it using

```php
Configure::write('Users.emailConfig', 'default');
```

Roles Management
----------------

You can add Users.roles on bootstrap.php file and these roles will be used on Admin Add / Edit pages. i.e:

```php
Configure::write('Users.roles', array('admin' => 'Admin', 'registered' => 'Registered'));
```

If you don't specify roles it will use 'admin' role (if is_admin is checked) or 'registered' role otherwise. You can override 'registered role setting Users.defaultRole on bootstrap.php. i.e:

```php
Configure::write('Users.defaultRole', 'user_registered');
```

Enabling / Disabling Registration
---------------------------------

Some application won't need to have registration enable so you can define ```Users.allowRegistration``` in ```bootstrap.php``` to enable or disable registration. By default registration will be enabled.

```
// Disables the registration
Configure::write('Users.allowRegistration', false);
```

Configuration options
---------------------

The configuration settings can be written by using the Configure class.

```
Users.disableDefaultAuth
```

Disables/enables the default auth setup that is implemented in the plugins UsersController::_setupAuth()

```
Users.allowRegistration
```

Disables/enables the user registration.

```
Users.roles
```

Optional array of user roles if you need it. This is not activly used by the plugin by default.

```
Users.sendPassword
```

Disables/enables the password reset functionality

```
Users.emailConfig
```

Email configuration settings array used by this plugin.
