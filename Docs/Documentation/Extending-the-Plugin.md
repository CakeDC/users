Extending the Plugin
====================

Extending the controller
------------------------

Declare the controller class. It is important to set the ```$name``` property here to make sure the controller initializes everything correct. If not setting the name the name setting is inherited and won't match the new controllers name.

```php
App::uses('UsersController', 'Users.Controller');
class AppUsersController extends UsersController {
	public $name = 'AppUsers';
}
```

In the case you want to extend also the user model it's required to set the right user class in the beforeFilter() because the controller will use the inherited model which would be Users.User.

```php
public function beforeFilter() {
	parent::beforeFilter();
	$this->User = ClassRegistry::init('AppUser');
	$this->set('model', 'AppUser');
}
```

You can overwrite the ```Controller::render()``` method to fall back to the plugin views in the case you want to use some of them

```php
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
```

Note: Depending on the CakePHP version you are using, you might need to bring a copy of the Views used in the plugin to your AppUsers view directory

Extending the model
-------------------

Declare the model. Same as in the controller, set the ```$name``` property and set the ```$useTable``` property to ```users```.

```php
App::uses('User', 'Users.Model');
class AppUser extends User {
	public $name = 'AppUser';
	public $useTable = 'users';
}
```

It's important to override the ```AppUser::$useTable``` property with the ```users``` table. It won't use the correct table otherwise.

You can override / extend all methods or properties like validation rules to suit your needs.

Routing to preserve the /users URL when extending the plugin
------------------------------------------------------------

See the [Routing](Routing.md) documentation.