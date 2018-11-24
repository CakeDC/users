Authentication
==============

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


The default configuration for Auth.Authenticators is:
```
[
    'Authentication.Session' => [
        'skipGoogleVerify' => true,
        'sessionKey' => 'Auth',
    ],
    'CakeDC/Auth.Form' => [
        'urlChecker' => 'Authentication.CakeRouter',
        'loginUrl' => [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'login',
            'prefix' => false,
        ]
    ],
    'Authentication.Token' => [
        'skipGoogleVerify' => true,
        'header' => null,
        'queryParam' => 'api_key',
        'tokenPrefix' => null,
    ],
    'CakeDC/Auth.Cookie' => [
        'skipGoogleVerify' => true,
        'rememberMeField' => 'remember_me',
        'cookie' => [
            'expires' => '1 month',
            'httpOnly' => true,
        ],
        'urlChecker' => 'Authentication.CakeRouter',
        'loginUrl' => [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'login',
            'prefix' => false,
        ]
    ],
    'CakeDC/Users.Social' => [
        'skipGoogleVerify' => true,
    ],
    'CakeDC/Users.SocialPendingEmail' => [
        'skipGoogleVerify' => true,
    ]
]
```

Check the documentation for [authenticators](https://github.com/cakephp/authentication/blob/master/docs/Authenticators.md)
