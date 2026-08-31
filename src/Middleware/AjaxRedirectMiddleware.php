<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Middleware;

use CakeDC\Users\Utility\AjaxFlash;
use CakeDC\Users\Utility\UsersUrl;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Rewrites redirect responses for Users-plugin ajax/HTMX requests.
 *
 * - HTMX request  -> 200 with an `HX-Redirect` header (HTMX performs the redirect client-side).
 * - JSON request  -> 200 with `{"success": <bool>, "redirect": "<location>", "flash": ...}`.
 *   `success` is false (and an `error` message is added) when the plugin queued an
 *   error flash before redirecting — e.g. a failed two-factor `verify`, which
 *   redirects back to the login action. A redirect alone never implies success.
 *
 * The middleware echoes the response's existing `Location` verbatim; it never
 * builds a destination from request input, so it adds no open-redirect surface of
 * its own. For the login flow that `Location` was already host-validated by
 * LoginComponent::afterIdentifyUser(); the other plugin redirects target fixed
 * plugin actions.
 */
class AjaxRedirectMiddleware implements MiddlewareInterface
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Psr\Http\Message\ResponseInterface A response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        // Scope to the configured Users controller (honors a custom `Users.controller`)
        // rather than a hardcoded plugin name, so an app controller is not left in a
        // half-enabled state where the component/templates emit hx-* but redirects
        // are never converted.
        /** @var \Cake\Http\ServerRequest $request */
        $scope = UsersUrl::actionParams('login');
        if (
            $request->getParam('plugin') !== $scope['plugin']
            || $request->getParam('controller') !== $scope['controller']
        ) {
            return $response;
        }

        $location = $response->getHeaderLine('Location');
        $status = $response->getStatusCode();
        if ($status < 300 || $status >= 400 || $location === '') {
            return $response;
        }

        /** @var \Cake\Http\Response $response */
        if ($request->hasHeader('HX-Request')) {
            return $response
                ->withStatus(200)
                ->withoutHeader('Location')
                ->withHeader('HX-Redirect', $location);
        }

        if ($request->is('json')) {
            $flash = AjaxFlash::consume($request->getSession());
            $success = ($flash['type'] ?? null) !== 'error';
            $payload = ['success' => $success, 'redirect' => $location, 'flash' => $flash];
            if (!$success) {
                $payload['error'] = $flash['message'];
            }

            return $response
                ->withStatus(200)
                ->withoutHeader('Location')
                ->withType('application/json')
                ->withStringBody((string)json_encode($payload));
        }

        return $response;
    }
}
