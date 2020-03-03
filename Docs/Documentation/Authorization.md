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
        'className' => 'CakeDC/Users.DefaultRedirect',
    ]
],
```

You can check the configuration options available for authorization middleware at the
[official documentation](https://github.com/cakephp/authorization/blob/master/docs/Middleware.md).

The `CakeDC/Users.DefaultRedirect` allows the option 'url' to be a normal cake url or callback to let
you create a custom logic to retrieve the unauthorized redirect url.

```
[
    'unauthorizedHandler' => [
        'className' => 'CakeDC/Users.DefaultRedirect',
        'url' => [
            'plugin' => false,
            'prefix' => false,
            'controller' => 'Pages',
            'action' => 'home'
         ]
    ]
],
```
OR
```
[
    'unauthorizedHandler' => [
        'className' => 'CakeDC/Users.DefaultRedirect',
        'url' => function($request, $options) {
              //custom logic

              return $url;
        }
    ]
],
```
Authorization Component
-----------------------
We autoload the authorization component at users controller using the default configuration,
if you don't want the plugin to autoload it, you can do:
```
Configure::write('Auth.AuthorizationComponent.enabled', false);
```

You can check the configuration options available for authorization component at the
[official documentation](https://github.com/cakephp/authorization/blob/master/docs/Component.md)

Authorization Service Loader
-----------------------------
To make the integration with cakephp/authorization easier we load the resolvers OrmResolver and MapResolver.
The MapResolver resolves ServerRequest request object to check access permission using Superuser and Rbac policies.

If the configuration is not enough for your project you may create a custom loader extending the
default provided.

- Create file src/Loader/AppAuthorizationServiceLoader.php

```
<?php
namespace App\Loader;

use \CakeDC\Users\Loader\AuthorizationServiceLoader;

class AppAuthorizationServiceLoader
{
    /**
     * Load the authorization service with OrmResolver and Map Resolver for RbacPolicy
     *
     * @param ServerRequestInterface $request The request.
     * @return AuthorizationService
     */
    public function __invoke(ServerRequestInterface $request)
    {
        $orm = new OrmResolver();

        $resolver = new ResolverCollection([
            $map,
            $orm
        ]);

        return new AuthorizationService($resolver);
    }
}
```
- Change the authorization service loader:

```
Configure::write('Authorization.serviceLoader', \App\Loader\AppAuthorizationServiceLoader::class);
```
