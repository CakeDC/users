<?php

namespace CakeDC\Users\Authenticator;

use Authentication\Authenticator\CookieAuthenticator as BaseAuthenticator;
use Authentication\Authenticator\PersistenceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Cookie Authenticator
 *
 * Authenticates an identity based on a cookies data.
 */
class CookieAuthenticator extends BaseAuthenticator implements PersistenceInterface
{
    /**
     * {@inheritDoc}
     */
    public function persistIdentity(ServerRequestInterface $request, ResponseInterface $response, $identity)
    {
        $field = $this->getConfig('rememberMeField');

        $bodyData = $request->getParsedBody();
        if (empty($bodyData)) {
            $session = $request->getAttribute('session');
            $bodyData = $session->read('CookieAuth');
            $session->delete('CookieAuth');
        }

        if (!$this->_checkUrl($request) || !is_array($bodyData) || empty($bodyData[$field])) {
            return [
                'request' => $request,
                'response' => $response
            ];
        }

        $value = $this->_createToken($identity);
        $cookie = $this->_createCookie($value);

        return [
            'request' => $request,
            'response' => $response->withAddedHeader('Set-Cookie', $cookie->toHeaderValue())
        ];
    }
}
