Authorization
=============
This plugin uses the new plugin for authorization [cakephp/authorization](https://github.com/cakephp/authorization/)
instead of CakePHP Authorization component, but don't worry, the default configuration should be enough for your
projects. We tried to allow you to start quickly without the need to configure a lot of things and also
allow you to configure as much as possible.


If you don't want the plugin to autoload setup authorization, you can do:
```
Configure::write('Auth.Authorization.enabled', false);
```

Authorization Middleware
------------------------
We load the RequestAuthorization and Authorization middleware with OrmResolver and RbacProvider(work with RequestAuthorizationMiddleware).

The middleware accepts some additional configurations, you can do:
```
Configure::write('Auth.AuthorizationMiddleware', $config);
```

The default configuration for authorization middleware is:
```
[
    'unauthorizedHandler' => [
        'exceptions' => [
            'MissingIdentityException' => 'Authorization\Exception\MissingIdentityException',
            'ForbiddenException' => 'Authorization\Exception\ForbiddenException',
        ],
        'className' => 'Authorization.CakeRedirect',
        'url' => [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'login',
        ]
    ]
],
```

You can check the configuration options available for authorization middleware at the 
[official documentation](https://github.com/cakephp/authorization/blob/master/docs/Middleware.md)


Authorization Component
-----------------------
We autoload the authorization component at users controller using the default configuration,
if you don't want the plugin to autoload it, you can do:
```
Configure::write('Auth.AuthorizationComponent.enabled', false);
``` 

You can check the configuration options available for authorization component at the 
[official documentation](https://github.com/cakephp/authorization/blob/master/docs/Component.md)
