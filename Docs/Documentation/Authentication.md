Authentication
==============
This component uses the new plugin for authentication [cakephp/authentication](https://github.com/cakephp/authentication/)
instead of CakePHP Authentication component, but the default configuration should be enough for your
projects. We tried to allow you to start quickly without the need to configure a lot of thing and also
allow you to configure as much as possible.

Authentication Component
-------------------------------

The default behavior is to load the authentication component at users controller, 
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
$user = $this->Authentication->getIdentity();
```
The default configuration for Auth.AuthenticationComponent is:

```
[
    'load' => true,
    'loginAction' => [
        'plugin' => 'CakeDC/Users',
        'controller' => 'Users',
        'action' => 'login',
        'prefix' => false,
    ],
    'logoutRedirect' => [
        'plugin' => 'CakeDC/Users',
        'controller' => 'Users',
        'action' => 'login',
        'prefix' => false,
    ],
    'loginRedirect' => '/',
    'requireIdentity' => false
]
```

[Check the component options at the it's source code for more infomation](https://github.com/cakephp/authentication/blob/master/src/Controller/Component/AuthenticationComponent.php#L38)

Authenticators
--------------

The cakephp/authentication plugin provider the main structure for the authenticators used in this plugin,
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
  
For example if you add JWT authenticator you can set:

```
$authenticators = Configure::read('Auth.Authenticators');
$authenticators['Authentication.Jwt'] = [
    'queryParam' => 'token',
    'skipGoogleVerify' => true,
]; 
Configure::write('Auth.Authenticators', $authenticators);

``` 
**You may have noticed the 'skipGoogleVerify' option, this option is used to identify if a authenticator should skip
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
```
[
    'Authentication.Password' => [],
    "CakeDC/Users.Social" => [
        'authFinder' => 'all'
    ], at load authentication
      service step method from plugin object
    'Authentication.Token' => [
        'tokenField' => 'api_token'
    ]
]
```
The identifiers are loaded by \CakeDC\Users\Loader\AuthenticationServiceLoader class at load authentication
service method from plugin object.

Authentication Service Loader 
-----------------------------
To make integration with cakephp/authentication easier we load the the authenticators and identifiers
defined at Auth configuration and other components to work with social provider, two-factor authentication.

If the configuration is not enough for your project you may create a custom loader extending the 
default provided.

- Create file src/Loader/AppAuthenticationServiceLoader.php

```
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
- Change the authentication service loader:

```
Configure::write('Authentication.serviceLoader', \CakeDC\Users\Loader\AuthenticationServiceLoader::class);
```