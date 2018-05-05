<?php

namespace CakeDC\Users\Middleware;

use Authentication\Authenticator\FormAuthenticator;
use Cake\Core\InstanceConfigTrait;
use Cake\Http\ServerRequest;
use Cake\Log\LogTrait;
use Cake\Routing\Router;
use Psr\Http\Message\ResponseInterface;

class GoogleAuthenticatorMiddleware
{
    use InstanceConfigTrait;
    use LogTrait;

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
        $identity = $request->getAttribute('identity');
        if (!$identity) {
            return $next($request, $response);
        }

        $service = $request->getAttribute('authentication');

        if ($service->getAuthenticationProvider()->getConfig('skipGoogleVerify') === true) {
            return $next($request, $response);
        }

        $result = $service->clearIdentity($request, $response);
        $request = $result['request'];
        $response = $result['response'];
        $request = $request->withoutAttribute('identity');
        $request = $request->withoutAttribute('authentication');
        $request = $request->withoutAttribute('authenticationResult');
        $request->getSession()->write('temporarySession', $identity->getOriginalData());
        $request->getSession()->write('CookieAuth', [
            'remember_me' => $request->getData('remember_me')
        ]);

        $url = Router::url(['action' => 'verify']);

        return $response
            ->withHeader('Location', $url)
            ->withStatus(302);

    }

}