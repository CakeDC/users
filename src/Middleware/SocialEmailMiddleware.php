<?php

namespace CakeDC\Users\Middleware;

use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;

class SocialEmailMiddleware extends SocialAuthMiddleware
{
    /**
     * Complete social auth with user without social e-mail
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @param callable $next Callback to invoke the next middleware.
     * @return \Psr\Http\Message\ResponseInterface A response
     */
    public function __invoke(ServerRequest $request, ResponseInterface $response, $next)
    {
        $action = $request->getParam('action');
        if ($action !== 'socialEmail' || $request->getParam('plugin') !== 'CakeDC/Users') {
            $request->getSession()->delete(Configure::read('Users.Key.Session.social'));

            return $next($request, $response);
        }

        if (!$request->getSession()->check(Configure::read('Users.Key.Session.social'))) {
            throw new NotFoundException();
        }

        return $this->goNext($request, $response, $next);
    }
}
