<?php

namespace CakeDC\Users\Middleware;

use Cake\Http\Exception\NotFoundException;
use CakeDC\Users\Controller\Traits\ReCaptchaTrait;
use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;

class SocialEmailMiddleware extends  SocialAuthMiddleware
{
    use ReCaptchaTrait;

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
            return $next($request, $response);
        }

        $this->setConfig(Configure::read('SocialAuthMiddleware'));

        return $this->handleAction($request, $response, $next);
    }

    /**
     * Handle social email step post.
     *
     * @param int $result authentication result
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @param callable $next Callback to invoke the next middleware.
     * @return \Psr\Http\Message\ResponseInterface A response
     */
    private function handleAction(ServerRequest $request, ResponseInterface $response, $next)
    {
        if (!$request->getSession()->check(Configure::read('Users.Key.Session.social'))) {
            throw new NotFoundException();
        }
        $request->getSession()->delete('Flash.auth');
        $result = false;

        if (!$request->is('post')) {
            return $this->finishWithResult($result, $request, $response, $next);
        }

        if (!$this->_validateRegisterPost($request)) {
            $this->authStatus = self::AUTH_ERROR_INVALID_RECAPTCHA;
        } else {
            $result = $this->authenticate($request);
        }

        return $this->finishWithResult($result, $request, $response, $next);
    }

    /**
     * Check the POST and validate it for registration, for now we check the reCaptcha
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @return bool
     */
    private function _validateRegisterPost($request)
    {
        if (!Configure::read('Users.reCaptcha.registration')) {
            return true;
        }

        return $this->validateReCaptcha(
            $request->getData('g-recaptcha-response'),
            $request->clientIp()
        );
    }

    /**
     * Authenticates with Session data (from SocialAuthMiddleware) and form email.
     * config: Users.Key.Session.social,
     * form input name: email
     *
     * @param \Cake\Http\ServerRequest $request Request object.
     * @return array|bool
     */
    protected function getUser(ServerRequest $request)
    {
        $data = $request->getSession()->read(Configure::read('Users.Key.Session.social'));
        $requestDataEmail = $request->getData('email');
        if (!empty($data) && empty($data['uid']) && (!empty($data['email']) || !empty($requestDataEmail))) {
            if (!empty($requestDataEmail)) {
                $data['email'] = $requestDataEmail;
            }
            $request->getSession()->delete(Configure::read('Users.Key.Session.social'));

            return $data;
        }

        return false;
    }
}