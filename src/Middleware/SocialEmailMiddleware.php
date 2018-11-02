<?php

namespace CakeDC\Users\Middleware;

use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\ServerRequest;
use CakeDC\Users\Exception\SocialAuthenticationException;
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
        if ($request->getAttribute('identity')) {
            $request->getSession()->delete(Configure::read('Users.Key.Session.social'));
        }
        $action = $request->getParam('action');
        if ($action !== 'socialEmail' || $request->getParam('plugin') !== 'CakeDC/Users') {
            return $next($request, $response);
        }

        return $this->handleAction($request, $response, $next);
    }

    /**
     * Handle social email step post.
     *
     * @param int $request authentication result
     * @param \Psr\Http\Message\ServerRequestInterface $response The request.
     * @param callable $next The response.
     * @return \Psr\Http\Message\ResponseInterface A response
     */
    private function handleAction(ServerRequest $request, ResponseInterface $response, $next)
    {
        if (!$request->getSession()->check(Configure::read('Users.Key.Session.social'))) {
            throw new NotFoundException();
        }
        try {
            return $next($request, $response);
        } catch (SocialAuthenticationException $exception) {
            return $this->onAuthenticationException($request, $response, $exception);
        }
    }
}
