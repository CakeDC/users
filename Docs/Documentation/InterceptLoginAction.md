Intercept Login Action
======================

There is a moment when you may want to intercept the login action to perform
some specific login, like redirect user to another url or set user data.
A simple way to intercept the login action is by creating a custom middleware, the following
example shows how to set user data and redirect to anothe url.

```php
<?php
namespace App\Middleware;

use CakeDC\Users\Utility\UsersUrl;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BeforeLoginMiddleware
{
    /**
     * My custom middleware
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @param callable $next Callback to invoke the next middleware.
     * @return \Psr\Http\Message\ResponseInterface A response
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
        if (!(new UsersUrl())->checkActionOnRequest('login', $request)) {
            return $next($request, $response);
        }

        if (!$request->getAttribute('session')->read('Auth')) {
            //do some logic
            //do more logic
            $request->getAttribute('session')->write('Auth', $userIdentity);

            $response = $response->withHeader('Location', '/pages/33');

            return $response;
        }

        return $next($request, $response);
    }

}
```

