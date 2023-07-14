Authentication
==============

This plugin uses the new CakePHP Authentication plugin [cakephp/authentication](https://github.com/cakephp/authentication/)
instead of CakePHP Authentication component, but don't worry, the default configuration should be enough for your
projects.

We've tried to simplify configuration as much as possible using defaults, but keep the ability to override them when needed.

Authentication Component
------------------------

The default behavior is to load the authentication component at ``UsersController``,
defining the default URLs for ``loginAction``, ``loginRedirect``, ``logoutRedirect`` but not requiring
the request to have an identity.

If you prefer to load the component yourself you can set ``Auth.AuthenticationComponent.load``:

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

The default configuration for ``Auth.AuthenticationComponent`` is:

```php
[
    'load' => true,
    'loginRedirect' => '/',
    'requireIdentity' => false
]
```

Check [the component options at the its source code](https://github.com/cakephp/authentication/blob/master/src/Controller/Component/AuthenticationComponent.php#L38) for more infomation.

Authenticators
--------------

The ``cakephp/authentication`` plugin provides the main structure for the authenticators used in this plugin,
we also use some custom authenticators to work with social providers, reCaptcha and cookie. The default
list of authenticators includes:

- ``Authentication.Session``
- ``CakeDC/Auth.Form``
- ``Authentication.Token``
- ``CakeDC/Auth.Cookie``
- ``CakeDC/Users.Social`` which works with the ``SocialAuthMiddleware``
- ``CakeDC/Users.SocialPendingEmail``

If you enable ``OneTimePasswordAuthenticator.login`` we also load the ``CakeDC/Auth.TwoFactor``

These authenticators should be enough for your application, but you can easily customize it
setting the ``Auth.Authenticators`` config key.

These authenticators are loaded by the ``\CakeDC\Users\Loader\AuthenticationServiceLoader`` class in the ``loadAuthenticators`` method. See [Authentication Service Loader](#authentication-service-loader) on how to adjust it to your needs.

For example, if you want to add the JWT authenticator you must add the following to your ``config/users.php`` file:

```php
'Auth.Authenticators.Jwt' => [
    'queryParam' => 'token',
    'skipTwoFactorVerify' => true,
    'className' => 'Authentication.Jwt',
],
```

The ``skipTwoFactorVerify`` option is used to skip the two factor flow for a given authenticator

Identifiers
-----------

The identifiers are defined to work correctly with the default authenticators, we are using these identifiers:

- ``Authentication.Password``, for ``Form`` authenticator
- ``CakeDC/Users.Social``, for ``Social`` and ``SocialPendingEmail`` authenticators
- ``Authentication.Token``, for ``Token`` authenticator

As you add more authenticators you may also need to add other identifiers, please see [the identifiers available in the official CakePHP Authentication plugin documentation](https://book.cakephp.org/authentication/2/en/identifiers.html).

The default list for ``Auth.Identifiers`` is:

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

These identifiers are loaded by the ``\CakeDC\Users\Loader\AuthenticationServiceLoader`` class in the ``loadIdentifiers`` method. See [Authentication Service Loader](#authentication-service-loader) on how to adjust it to your needs.

Handling Login Result
---------------------

For both form login and social login we use a base component ``CakeDC/Users.Login`` to handle the login.
It checks the result of the authentication service and either redirects the user or shows an authentication
error. It provides some error messages for specific authentication results. Please check the ``config/users.php`` file.

To use a custom component to handle the login you should update your ``config/users.php`` file with:

```php
'Auth.SocialLoginFailure.component' => 'MyLoginA',
'Auth.FormLoginFailure.component' => 'MyLoginB',
```

The default configuration is:

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

To make the integration with CakePHP Authenication plugin easier we load the authenticators and identifiers
defined at the ``Auth`` configuration key.

If the default configuration is not enough for your project's needs you may create a custom loader extending the
default loader provided.

For example, create a file ``src/Loader/AppAuthenticationServiceLoader.php``:

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

Add the following to your ``config/users.php`` configuration to change the authentication service loader:

```php
'Auth.Authentication.serviceLoader' => \App\Loader\AppAuthenticationServiceLoader::class,
```
