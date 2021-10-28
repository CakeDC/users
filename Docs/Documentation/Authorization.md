Authorization
=============
This plugin uses the new plugin for authorization [cakephp/authorization](https://github.com/cakephp/authorization/)
instead of CakePHP Authorization component, but don't worry, the default configuration should be enough for your
projects. We tried to allow you to start quickly without the need to configure a lot of things and also
allow you to configure as much as possible.


If you don't want the plugin to autoload setup authorization, you can disable
in your config/users.php with:

```php
'Auth.Authorization.enabled' => false,
```

Authorization Middleware
------------------------
We load the RequestAuthorization and Authorization middleware with OrmResolver and RbacProvider(work with RequestAuthorizationMiddleware).

The middleware accepts some additional configurations, you can update in your
config/users.php file:
```php
'Auth.AuthorizationMiddleware' => $config,
```

The default configuration for authorization middleware is:
```php
[
    'unauthorizedHandler' => [
        'className' => 'CakeDC/Users.DefaultRedirect',
    ]
],
```

You can check the configuration options available for authorization middleware at the
[official documentation](https://github.com/cakephp/authorization/blob/master/docs/en/middleware.rst).

The `CakeDC/Users.DefaultRedirect` offers additional behavior and config:
  * If logged user access unauthorized url he is redirected to referer url or '/' if no referer url
  * If not logged user access unauthorized url he is redirected to configured url (default to login)
  * on login we only use the redirect url from querystring 'redirect' if user can access the target url
  * App can configure a callable for 'url' option to define a custom logic to retrieve the url for unauthorized redirect
  * App can configure a flash message

You could do the following to set a custom url and flash message:

```php
[
    'unauthorizedHandler' => [
        'className' => 'CakeDC/Users.DefaultRedirect',
        'url' => [
            'plugin' => false,
            'prefix' => false,
            'controller' => 'Pages',
            'action' => 'home'
        ],
        'flash' => [
            'message' => 'My custom message',
            'key' => 'flash',
            'element' => 'flash/error',
            'params' => [],
        ],
    ]
],
```
OR
```php
[
    'unauthorizedHandler' => [
        'className' => 'CakeDC/Users.DefaultRedirect',
        'url' => function($request, $options) {
              //custom logic

              return $url;
        },
        'flash' => [
            'message' => 'My custom message',
            'key' => 'flash',
            'element' => 'flash/error',
            'params' => [],
        ],
    ]
],
```
Authorization Component
-----------------------
We autoload the authorization component at users controller using the default configuration,
if you don't want the plugin to autoload it, you can add this to your config/users.php file:

```php
'Auth.AuthorizationComponent.enabled' => false,
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

```php
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
- Add this to your config/users.php file to change the authorization service loader:

```php
'Auth.Authorization.serviceLoader' => \App\Loader\AppAuthorizationServiceLoader::class,
```
