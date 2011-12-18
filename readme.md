# Users Plugin for CakePHP #

The users plugin is for allowing users to register and login manage their profile. It also allows admins to manage the users.

The plugin is thought as a base to extend your app specific users controller and model from.

## Installation ##

The plugin is pretty easy to set up, all you need to do is to copy it to you application plugins folder and load the needed tables. You can create database tables using either the schema shell or the [CakeDC Migrations plugin](http://github.com/CakeDC/migrations):

	cake schema create users -p Users

or

	cake Migrations.migration all --plugin Users

You will also need the [CakeDC Search plugin](http://github.com/CakeDC/search), just grab it and put it into your application's plugin folder.

## How to use it ##

You can use the plugin as it comes if you're happy with it or, more common, extend your app specific user implementation from the plugin.

The plugin itself is already capable of:

* User registration
* Account verification by a token sent via email
* User login (email / password)
* Password reset based on requesting a token by email and entering a new password
* Simple profiles for users
* User search (requires the CakeDC Search plugin)
* User management using the "admin" section

The default password reset process requires the user to enter his email address, an email is sent to the user with a link and a token. When the user accesses the URL with the token he can enter a new password.

### Using the "remember me" cookie ###

To use the "remember me" checkbox which sets a cookie on the login page you will need to put this code or method call in your AppController::beforeFilter() method.

	public function restoreLoginFromCookie() {
		$this->Cookie->name = 'Users';
		$cookie = $this->Cookie->read('rememberMe');
		if (!empty($cookie) && !$this->Auth->user()) {
			$data['User'][$this->Auth->fields['username']] = $cookie[$this->Auth->fields['username']];
			$data['User'][$this->Auth->fields['password']] = $cookie[$this->Auth->fields['password']];
			$this->Auth->login($data);
		}
	}

The code will read the login credentials from the cookie and log the user in based on that information. Do not forget to change the cookie name or fields to what you are using if you have changed them in your application!

## How to extend the plugin ##

### Changing the default "from" email setting ###

To change the plugins default "from" setting for outgoing emails put this into your bootstrap.php

	Configure::write('App.defaultEmail', your@email.com);

If not configured it will use 'noreply@' . env('HTTP_HOST'); as default from email address.

### Extending the controller ###

Declare the controller class

	App::uses('Controller', 'Users.Users');
	AppUsersController extends UsersController

In the case you want to extend also the user model it's required to set the right user class in the beforeFilter() because the controller will use the inherited model which would be Users.User.

	public function beforeFilter() {
		parent::beforeFilter();
		$this->User = ClassRegistry::init('AppUser');
	}

You can overwrite the render() method to fall back to the plugin views in the case you want to use some of them

	public function render($action = null, $layout = null, $file = null) {
		if (is_null($action)) {
			$action = $this->action;
		}
		if (!file_exists(VIEWS . $this->viewPath . DS . $action . '.ctp')) {
			$file = App::pluginPath('users') . 'views' . DS . 'users' . DS . $action . '.ctp';
		}
		return parent::render($action, $layout, $file);
	}

### Extending the model ###

Declare the model 

	App::uses('Model', 'Users.User');
	AppUser extends User {
		public $useTable = 'users';
		public $name = 'AppUser';
	}

It's important to override the AppUser::useTable property with the 'users' table.

You can override/extend all methods or properties like validation rules to suit your needs.

### Routes for pretty URLs ###

To remove the second users from /users/users in the url you can use routes.

The plugin itself comes with a routes file but you need to explicitly load them. 

	CakePlugin::load('Users', array('routes' => true));

List of the used routes:

	Router::connect('/users', array('plugin' => 'users', 'controller' => 'users'));
	Router::connect('/users/index/*', array('plugin' => 'users', 'controller' => 'users'));
	Router::connect('/users/:action', array('plugin' => 'users', 'controller' => 'users'));
	Router::connect('/users/users/:action', array('plugin' => 'users', 'controller' => 'users'));
	Router::connect('/login/*', array('plugin' => 'users', 'controller' => 'users', 'action' => 'login'));
	Router::connect('/logout/*', array('plugin' => 'users', 'controller' => 'users', 'action' => 'logout'));
	Router::connect('/register/*', array('plugin' => 'users', 'controller' => 'users', 'action' => 'add'));

If you're extending the plugin remove the plugin from the route by setting it to null and replace the controller with your controller extending the plugins users controller.

Feel free to change the routes here or add others as you need for your application.

## Requirements ##

* PHP version: PHP 5.2+
* CakePHP version: Cakephp 2.0
* [CakeDC Utils plugin](http://github.com/CakeDC/utils)
* [CakeDC Search plugin](http://github.com/CakeDC/search)

## Support ##

For support and feature request, please visit the [Users Plugin Support Site](http://cakedc.lighthouseapp.com/projects/60126-users-plugin/).

For more information about our Professional CakePHP Services please visit the [Cake Development Corporation website](http://cakedc.com).

## License ##

Copyright 2009-2010, [Cake Development Corporation](http://cakedc.com)

Licensed under [The MIT License](http://www.opensource.org/licenses/mit-license.php)<br/>
Redistributions of files must retain the above copyright notice.

## Copyright ###

Copyright 2009-2010<br/>
[Cake Development Corporation](http://cakedc.com)<br/>
1785 E. Sahara Avenue, Suite 490-423<br/>
Las Vegas, Nevada 89104<br/>
http://cakedc.com<br/>

