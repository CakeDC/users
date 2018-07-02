<?php

namespace CakeDC\Users\Middleware;

use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use CakeDC\Users\Authentication\AuthenticationService;
use Psr\Http\Message\ResponseInterface;

class GoogleAuthenticatorMiddleware
{
    /**
     * Proceed to second step of two factor authentication. See CakeDC\Users\Controller\Traits\verify
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @param callable $next Callback to invoke the next middleware.
     * @return \Psr\Http\Message\ResponseInterface A response
     */
    public function __invoke(ServerRequest $request, ResponseInterface $response, $next)
    {
        $service = $request->getAttribute('authentication');

        if (!$service->getResult() || $service->getResult()->getStatus() !== AuthenticationService::NEED_GOOGLE_VERIFY) {
            return $next($request, $response);
        }

        $request->getSession()->write('CookieAuth', [
            'remember_me' => $request->getData('remember_me')
        ]);

        $url = Router::url([
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'verify'
        ]);

        return $response
            ->withHeader('Location', $url)
            ->withStatus(302);
    }

}