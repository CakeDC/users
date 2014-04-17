How to use it
=============

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

Using the "remember me" functionality
-------------------------------------

To use the "remember me" checkbox which sets a cookie on the login page you will need to add the RememberMe component to the AppController or the controllers you want to auto-login the user again based on the cookie.

```php
public $components = array(
	'Users.RememberMe'
);
```

If you are using another user model than 'User' you'll have to configure it:

	public $components = array(
		'Users.RememberMe' => array(
			'userModel' => 'AppUser');

And add this line

```php
$this->RememberMe->restoreLoginFromCookie()
```

to your controllers beforeFilter() callack

```php
public function beforeFilter() {
	parent::beforeFilter();
	$this->RememberMe->restoreLoginFromCookie();
}
```

The code will read the login credentials from the cookie and log the user in based on that information. Note that you have to use CakePHPs AuthComponent or an aliased Component implementing the same interface as AuthComponent.
