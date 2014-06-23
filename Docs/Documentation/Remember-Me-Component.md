Remember Me Component
=====================

To use the "remember me" checkbox which sets a cookie on the login page you will need to add the RememberMe component to the AppController or the controllers you want to auto-login the user again based on the cookie.

```php
public $components = array(
	'Users.RememberMe'
);
```

If you are using another user model than ```User``` you'll have to configure it:

```php
	public $components = array(
		'Users.RememberMe' => array(
			'userModel' => 'AppUser'
		)
	);
```

And add this line

```php
$this->RememberMe->restoreLoginFromCookie();
```

to your controllers ```beforeFilter()``` callback

```php
public function beforeFilter() {
	parent::beforeFilter();
	$this->RememberMe->restoreLoginFromCookie();
}
```

The code will read the login credentials from the cookie and log the user in based on that information. Note that you have to use CakePHPs AuthComponent or an aliased Component implementing the same interface as AuthComponent.
