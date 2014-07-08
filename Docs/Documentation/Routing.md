Routing
=======

To remove the second users from ```/users/users``` in the url you can use routes.

The plugin itself comes with a routes file but you need to explicitly load them.

```php
CakePlugin::load('Users', array(
	'routes' => true
));
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

Changing the routes
-------------------

If you're extending the plugin remove the plugin from the route by setting it to ```null``` and replace the controller with your controller extending the plugins users controller.

```php
Router::connect('/users', array('plugin' => null, 'controller' => 'app_users'));
/* ... */
```

Feel free to change the routes here or add others as you need for your application.