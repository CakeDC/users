Auth in the Users Plugin
========================

The users plugin is made to work out of the box with some default settings. If you want to customize them or user your application level auth settings you'll have to do a few things that are explained in this document.

Disable The Default Auth Settings
---------------------------------

In the case you want to user your customized auth settings of your application, for example declared in the ```AppController::beforeFilter()``` method, you'll have to disable the default auth of the users plugin.

You can use the configuration settings to disable it, for example in your ```bootstrap.php```

```php
Configure::write('Users.disableDefaultAuth');
```

Or when extending the UsersController simply overwrite the ```_setupAuth()``` method.

```php
protected function _setupAuth() {
}
```

Overwriting the default auth settings
-------------------------------------

If you want to change some of the default auth settings of the users controller overwrite the ```_setupAuth()``` method in the extending controller.

```php
protected function _setupAuth() {
	parent::_setupAuth();
	$this->Auth->loginRedirect = array(
		'plugin' => null,
		'admin' => false,
		'controller' => 'app_users',
		'action' => 'login'
	);
}
```