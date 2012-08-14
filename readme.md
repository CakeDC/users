# Users Plugin for CakePHP #

for cake 2.x

The users plugin is for allowing users to register and login manage their profile. It also allows admins to manage the users.

The plugin is thought as a base to extend your app specific users controller and model from.

That it works out of the box does not mean it is thought to be used exactly like it is but to provide you a kick start. You will have to extend the plugin on app level to customize it. Read the how to use it instructions carefully.

## Installation ##

The plugin is pretty easy to set up, all you need to do is to copy it to you application plugins folder and load the needed tables. You can create database tables using either the schema shell or the [CakeDC Migrations plugin](http://github.com/CakeDC/migrations):

	./Console/cake schema create users --plugin Users

or

	./Console/cake Migrations.migration all --plugin Users

You will also need the [CakeDC Search plugin](http://github.com/CakeDC/search), just grab it and put it into your application's plugin folder.

## How to use it ##

You can use the plugin as it comes if you're happy with it or, more common, extend your app specific user implementation from the plugin.

The plugin itself is already capable of:

* User registration (Enable by default)
* Account verification by a token sent via email
* User login (email / password)
* Password reset based on requesting a token by email and entering a new password
* Simple profiles for users
* User search (requires the CakeDC Search plugin)
* User management using the "admin" section (add / edit / delete)
* Simple roles management

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

	App::uses('UsersController', 'Users.Controller');
	class AppUsersController extends UsersController {
	}

In the case you want to extend also the user model it's required to set the right user class in the beforeFilter() because the controller will use the inherited model which would be Users.User.

	public function beforeFilter() {
		parent::beforeFilter();
		$this->User = ClassRegistry::init('AppUser');
	}

You can overwrite the render() method to fall back to the plugin views in the case you want to use some of them

	public function render($view = null, $layout = null) {
		if (is_null($view)) {
			$view = $this->action;
		}
		$viewPath = substr(get_class($this), 0, strlen(get_class($this)) - 10);
		if (!file_exists(APP . 'View' . DS . $viewPath . DS . $view . '.ctp')) {
			$this->plugin = 'Users';
		} else {
			$this->viewPath = $viewPath;
		}
		return parent::render($view, $layout);
	}

### Overwriting the default auth settings provided by the plugin

To use the basics the plugin already offers but changing some of the settings overwrite the _setupAuth() method in the extending controller.

	protected function _setupAuth() {
		parent::_setupAuth();

		$this->Auth->loginRedirect = array('plugin' => null, 'admin' => false, 'controller' => 'app_users', 'action' => 'login');
	}

If you want to disable it simply overwrite it without any body

	protected function _setupAuth() {
	}

### Extending the model ###

Declare the model 

	App::uses('User', 'Users.Model');
	class AppUser extends User {
		public $useTable = 'users';
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
	Router::connect('/users/:action/*', array('plugin' => 'users', 'controller' => 'users'));
	Router::connect('/users/users/:action/*', array('plugin' => 'users', 'controller' => 'users'));
	Router::connect('/login/*', array('plugin' => 'users', 'controller' => 'users', 'action' => 'login'));
	Router::connect('/logout/*', array('plugin' => 'users', 'controller' => 'users', 'action' => 'logout'));
	Router::connect('/register/*', array('plugin' => 'users', 'controller' => 'users', 'action' => 'add'));

If you're extending the plugin remove the plugin from the route by setting it to null and replace the controller with your controller extending the plugins users controller.

Feel free to change the routes here or add others as you need for your application.

### Email Templates

To modify the templates as needed copy them to

	/app/View/Plugin/Users/Emails/

Note that you will have to overwrite any view that is linking to the plugin like the email verification email.

## Configuration options


### Disable Slugs 

If the Utils plugin is present the users model will auto attach and use the sluggable behavior.

To not create slugs for a new user records put this in your configuration: Configure::write('Users.disableSlugs', true);

### Email configuration

The plugin uses the $default email configuration (should be present in your Config/email.php file), but you can override it using

	Configure::write('Users.emailConfig', 'default');

## Roles Management

You can add Users.roles on bootstrap.php file and these roles will be used on Admin Add / Edit pages. i.e:

	Configure::write('Users.roles', array('admin' => 'Admin', 'registered' => 'Registered'));

If you don't specify roles it will use 'admin' role (if is_admin is checked) or 'registered' role otherwise. You can override 'registered role setting Users.defaultRole on bootstrap.php. i.e:

	Configure::write('Users.defaultRole', 'user_registered');

## Enabling / Disabling Registration

Some application won't need to have registration enable so you can define Users.allowRegistration on bootstrap.php to enable / disable registration. By default registration will be enabled.

## Requirements ##

* PHP version: PHP 5.2+
* CakePHP version: Cakephp 2.0
* [CakeDC Utils plugin](http://github.com/CakeDC/utils)
* [CakeDC Search plugin](http://github.com/CakeDC/search)

## Support ##

For support and feature request, please visit the [Users Plugin Support Site](http://cakedc.lighthouseapp.com/projects/60126-users-plugin/).

For more information about our Professional CakePHP Services please visit the [Cake Development Corporation website](http://cakedc.com).

## Branch strategy ##

The master branch holds the STABLE latest version of the plugin. 
Develop branch is UNSTABLE and used to test new features before releasing them. 

Previous maintenance versions are named after the CakePHP compatible version, for example, branch 1.3 is the maintenance version compatible with CakePHP 1.3.
All versions are updated with security patches.

## Contributing to this Plugin ##

Please feel free to contribute to the plugin with new issues, requests, unit tests and code fixes or new features. If you want to contribute some code, create a feature branch from develop, and send us your pull request. Unit tests for new features and issues detected are mandatory to keep quality high. 


## License ##

Copyright 2009-2012, [Cake Development Corporation](http://cakedc.com)

Licensed under [The MIT License](http://www.opensource.org/licenses/mit-license.php)<br/>
Redistributions of files must retain the above copyright notice.

## Copyright ###

Copyright 2009-2012<br/>
[Cake Development Corporation](http://cakedc.com)<br/>
1785 E. Sahara Avenue, Suite 490-423<br/>
Las Vegas, Nevada 89104<br/>
http://cakedc.com<br/>

