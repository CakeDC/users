Intercept Login Action
======================

There is a moment when you may want to intercept the login action to perform
some specific login, like redirect user to another url or set user data.
A simple way to intercept the login action is by creating a custom middleware, the following
example shows how to set user data and redirect to anothe url.

```php
<?php
namespace App\Middleware;

use Cake\Http\Response;
use CakeDC\Users\Utility\UsersUrl;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class BeforeLoginMiddleware implements MiddlewareInterface
{
    /**
     * My custom middleware
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Psr\Http\Message\ResponseInterface A response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!(new UsersUrl())->checkActionOnRequest('login', $request)) {
            return $handler->handle($request);
        }

        if (!$request->getAttribute('session')->read('Auth')) {
            //do some logic
            $request->getAttribute('session')->write('Auth', $userIdentity);

            $response = $response->withHeader('Location', '/pages/33');
            return (new Response())->withHeader('Location', '/pages/33');
        }

        return $handler->handle($request);
    }

}
```

