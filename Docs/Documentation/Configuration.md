Routes for pretty URLs
----------------------

To remove the second users from /users/users in the url you can use routes.

The plugin itself comes with a routes file but you need to explicitly load them.

```php
CakePlugin::load('Users', array('routes' => true));
```

List of the used routes:

```php
Router::connect('/users', array('plugin' => 'users', 'controller' => 'users'));
Router::connect('/users/index/*', array('plugin' => 'users', 'controller' => 'users'));
Router::connect('/users/:action/*', array('plugin' => 'users', 'controller' => 'users'));
Router::connect('/users/users/:action/*', array('plugin' => 'users', 'controller' => 'users'));
Router::connect('/login/*', array('plugin' => 'users', 'controller' => 'users', 'action' => 'login'));
Router::connect('/logout/*', array('plugin' => 'users', 'controller' => 'users', 'action' => 'logout'));
Router::connect('/register/*', array('plugin' => 'users', 'controller' => 'users', 'action' => 'add'));
```

If you're extending the plugin remove the plugin from the route by setting it to null and replace the controller with your controller extending the plugins users controller.

Feel free to change the routes here or add others as you need for your application.

Email Templates
---------------

To modify the templates as needed copy them to

```
/app/View/Plugin/Users/Emails/
```

Note that you will have to overwrite any view that is linking to the plugin like the email verification email.

## Configuration options

### Disable Slugs

If the Utils plugin is present the users model will auto attach and use the sluggable behavior.

To not create slugs for a new user records put this in your configuration: Configure::write('Users.disableSlugs', true);

### Email configuration

The plugin uses the $default email configuration (should be present in your Config/email.php file), but you can override it using

```php
Configure::write('Users.emailConfig', 'default');
```

## Roles Management

You can add Users.roles on bootstrap.php file and these roles will be used on Admin Add / Edit pages. i.e:

```php
Configure::write('Users.roles', array('admin' => 'Admin', 'registered' => 'Registered'));
```

If you don't specify roles it will use 'admin' role (if is_admin is checked) or 'registered' role otherwise. You can override 'registered role setting Users.defaultRole on bootstrap.php. i.e:

```php
Configure::write('Users.defaultRole', 'user_registered');
```

## Enabling / Disabling Registration

Some application won't need to have registration enable so you can define Users.allowRegistration on bootstrap.php to enable / disable registration. By default registration will be enabled.

## Configuration options

The configuration settings can be written by using the Configure class.

	Users.disableDefaultAuth

Disables/enables the default auth setup that is implemented in the plugins UsersController::_setupAuth()

	Users.allowRegistration

Disables/enables the user registration.

	Users.roles

Optional array of user roles if you need it. This is not activly used by the plugin by default.

	Users.sendPassword

Disables/enables the password reset functionality

	Users.emailConfig

Email configuration settings array used by this plugin

Events
------

Events follow these conventions:

* Users.Controller.Users.someCallBack
* Users.Model.User.someCallBack
* ...

Triggered events are:

 * Users.Controller.Users.beforeRegister
 * Users.Controller.Users.afterRegister
 * Users.Controller.Users.beforeLogin
 * Users.Controller.Users.afterLogin
 * Users.Model.User.beforeRegister
 * Users.Model.User.afterRegister