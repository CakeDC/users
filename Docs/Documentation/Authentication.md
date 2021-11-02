Authentication
==============
This plugin uses the new authentication plugin [cakephp/authentication](https://github.com/cakephp/authentication/)
instead of CakePHP Authentication component, but don't worry, the default configuration should be enough for your
projects.

We've tried to simplify configuration as much as possible using defaults, but keep the ability to override them when needed.

Authentication Component
------------------------

The default behavior is to load the authentication component at UsersController,
defining the default urls for loginAction, loginRedirect, logoutRedirect but not requiring
the request to have a identity.

If you prefer to load the component yourself you can set 'Auth.AuthenticationComponent.load':

```
Configure:write('Auth.AuthenticationComponent.load', false);
```

And load the component at any controller:

```
$authenticationConfig = Configure::read('Auth.AuthenticationComponent');
$this->loadComponent('Authentication.Authentication', $authenticationConfig);
$userId = $this->Authentication->getIdentity()->getIdentifier();
$user = $this->Authentication->getIdentity()->getOriginalData();
```
The default configuration for Auth.AuthenticationComponent is:

```php
[
    'load' => true,
    'loginRedirect' => '/',
    'requireIdentity' => false
]
```

[Check the component options at the it's source code for more infomation](https://github.com/cakephp/authentication/blob/master/src/Controller/Component/AuthenticationComponent.php#L38)

Authenticators
--------------

The cakephp/authentication plugin provides the main structure for the authenticators used in this plugin,
we also use some custom authenticators to work with social providers, reCaptcha and cookie. The default
list of authenticators includes:

- 'Authentication.Session'
- 'CakeDC/Auth.Form'
- 'Authentication.Token'
- 'CakeDC/Auth.Cookie'
- 'CakeDC/Users.Social'//Works with SocialAuthMiddleware
- 'CakeDC/Users.SocialPendingEmail'

**If you enable 'OneTimePasswordAuthenticator.login' we also load the CakeDC/Auth.TwoFactor**

These authenticators should be enough for your application, but you easily customize it
setting the Auth.Authenticators config key.

For example if you add JWT authenticator you must add this to your config/users.php file:

```php
'Auth.Authenticators.Jwt' => [
    'queryParam' => 'token',
    'skipTwoFactorVerify' => true,
    'className' => 'Authentication.Jwt',
],
```

**You may have noticed the 'skipTwoFactorVerify' option, this option is used to identify if a authenticator should skip
the two factor flow**

The authenticators are loaded by \CakeDC\Users\Loader\AuthenticationServiceLoader class at load authentication
service method from plugin object.

See the full Auth.Authenticators at config/users.php

Identifiers
-----------
The identifies are defined to work correctly with the default authenticators, we are using these identifiers:

- Authentication.Password, for Form authenticator
- CakeDC/Users.Social, for Social and SocialPendingEmail authenticators
- Authentication.Token, for TokenAuthenticator

As you add more authenticators you may need to add identifiers, please check identifiers available at
[official documentation](https://github.com/cakephp/authentication/blob/master/docs/Identifiers.md)

The default value for Auth.Identifiers is:

```php
[
    'Password' => [
        'className' => 'Authentication.Password',
        'fields' => [
            'username' => ['username', 'email'],
            'password' => 'password'
        ],
        'resolver' => [
            'className' => 'Authentication.Orm',
            'finder' => 'active'
        ],
    ],
    "Social" => [
        'className' => 'CakeDC/Users.Social',
        'authFinder' => 'active'
    ],
    'Token' => [
        'className' => 'Authentication.Token',
        'tokenField' => 'api_token',
        'resolver' => [
            'className' => 'Authentication.Orm',
            'finder' => 'active'
        ],
    ]
]
```
The identifiers are loaded by \CakeDC\Users\Loader\AuthenticationServiceLoader class at load authentication
service method from plugin object.


Handling Login Result
---------------------
For both form login and social login we use a base component 'CakeDC/Users.Login' to handle login,
it check the result of authentication service to redirect user to a internal page or show an authentication
error. It provide some error messages for specific authentication result status, please check the config/users.php file.

To use a custom component to handle the login you should update your config/users.php file with:

```php
'Auth.SocialLoginFailure.component' => 'MyLoginA',
'Auth.FormLoginFailure.component' => 'MyLoginB',
```

The default configuration are:
```php
[
    ...
    'Auth' => [
        ...
        'SocialLoginFailure' => [
            'component' => 'CakeDC/Users.Login',
            'defaultMessage' => __d('cake_d_c/users', 'Could not proceed with social account. Please try again'),
            'messages' => [
                'FAILURE_USER_NOT_ACTIVE' => __d(
                    'cake_d_c/users',
                    'Your user has not been validated yet. Please check your inbox for instructions'
                ),
                'FAILURE_ACCOUNT_NOT_ACTIVE' => __d(
                    'cake_d_c/users',
                    'Your social account has not been validated yet. Please check your inbox for instructions'
                )
            ],
            'targetAuthenticator' => 'CakeDC\Users\Authenticator\SocialAuthenticator'
        ],
        'FormLoginFailure' => [
            'component' => 'CakeDC/Users.Login',
            'defaultMessage' => __d('cake_d_c/users', 'Username or password is incorrect'),
            'messages' => [
                'FAILURE_INVALID_RECAPTCHA' => __d('cake_d_c/users', 'Invalid reCaptcha'),
            ],
            'targetAuthenticator' => 'CakeDC\Auth\Authenticator\FormAuthenticator'
        ]
        ...
    ]
]
```

Authentication Service Loader
-----------------------------
To make the integration with cakephp/authentication easier we load the authenticators and identifiers
defined at Auth configuration and other components to work with social provider, two-factor authentication.

If the configuration is not enough for your project you may create a custom loader extending the
default provided.

- Create file src/Loader/AppAuthenticationServiceLoader.php

```php
<?php
namespace App\Loader;

use \CakeDC\Users\Loader\AuthenticationServiceLoader;

class AppAuthenticationServiceLoader extends AuthenticationServiceLoader
{
    /**
     * Load the authenticators with my custom condition
     *
     * @param \CakeDC\Auth\Authentication\AuthenticationService $service Authentication service to load identifiers
     *
     * @return void
     */
    protected function loadAuthenticators($service)
    {
        parent::loadAuthenticators($service);

        if (\Cake\Core\Configure::read('MyApp.enabledCustom')) {
            $service->loadAuthenticator('MyCustom', []);
        }
    }
}
```
- Add this to your config/users.php file to change the authentication service loader:

```php
'Auth.Authentication.serviceLoader' => \App\Loader\AuthenticationServiceLoader::class,
```
