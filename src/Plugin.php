<?php
/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users;

use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceProviderInterface;
use Authorization\Middleware\AuthorizationMiddleware;
use Authorization\Middleware\RequestAuthorizationMiddleware;
use Authorization\Policy\MapResolver;
use Authorization\Policy\OrmResolver;
use Authorization\Policy\ResolverCollection;
use CakeDC\Auth\Authentication\AuthenticationService;
use CakeDC\Auth\Middleware\OneTimePasswordAuthenticatorMiddleware;
use CakeDC\Auth\Middleware\RbacMiddleware;
use CakeDC\Auth\Policy\RbacPolicy;
use CakeDC\Users\Middleware\SocialAuthMiddleware;
use CakeDC\Users\Middleware\SocialEmailMiddleware;
use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Http\MiddlewareQueue;
use Cake\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Plugin extends BasePlugin implements AuthenticationServiceProviderInterface, AuthorizationServiceProviderInterface
{
    const EVENT_AFTER_LOGIN = 'Users.Authentication.afterLogin';
    const EVENT_BEFORE_LOGOUT = 'Users.Authentication.beforeLogout';
    const EVENT_AFTER_LOGOUT = 'Users.Authentication.afterLogout';

    const EVENT_BEFORE_REGISTER = 'Users.Managment.beforeRegister';
    const EVENT_AFTER_REGISTER = 'Users.Managment.afterRegister';
    const EVENT_AFTER_CHANGE_PASSWORD = 'Users.Managment.afterResetPassword';
    const EVENT_BEFORE_SOCIAL_LOGIN_USER_CREATE = 'Users.Managment.beforeSocialLoginUserCreate';
    const EVENT_ON_EXPIRED_TOKEN = 'Users.Managment.onExpiredToken';
    const EVENT_AFTER_RESEND_TOKEN_VALIDATION = 'Users.Managment.afterResendTokenValidation';

    /**
     * Returns an authentication service instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request
     * @param \Psr\Http\Message\ResponseInterface $response Response
     * @return \Authentication\AuthenticationServiceInterface
     */
    public function getAuthenticationService(ServerRequestInterface $request, ResponseInterface $response)
    {
        $key = 'Auth.Authentication.serviceLoader';

        return $this->loadService($request, $response, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationService(ServerRequestInterface $request, ResponseInterface $response)
    {
        $serviceLoader = Configure::read('Auth.Authorization.serviceLoader');
        if (is_string($serviceLoader)) {
            $serviceLoader = new $serviceLoader();
        }

        return $serviceLoader($request, $response);
    }

    /**
     * load authenticators and identifiers
     *
     * @return AuthenticationServiceInterface
     */
    public function authentication()
    {
        $service = new AuthenticationService();
        $authenticators = Configure::read('Auth.Authenticators');
        $identifiers = Configure::read('Auth.Identifiers');

        foreach ($identifiers as $identifier => $options) {
            if (is_numeric($identifier)) {
                $identifier = $options;
                $options = [];
            }

            $service->loadIdentifier($identifier, $options);
        }

        foreach ($authenticators as $authenticator => $options) {
            if (is_numeric($authenticator)) {
                $authenticator = $options;
                $options = [];
            }

            $service->loadAuthenticator($authenticator, $options);
        }

        if (Configure::read('Users.OneTimePasswordAuthenticator.login')) {
            $service->loadAuthenticator('CakeDC/Auth.TwoFactor', [
                'skipGoogleVerify' => true,
            ]);
        }

        return $service;
    }

    /**
     * {@inheritdoc}
     */
    public function middleware($middlewareQueue)
    {
        if (Configure::read('Users.Social.login')) {
            $middlewareQueue
                ->add(SocialAuthMiddleware::class)
                ->add(SocialEmailMiddleware::class);
        }

        $authentication = new AuthenticationMiddleware($this);
        $middlewareQueue->add($authentication);

        if (Configure::read('Users.OneTimePasswordAuthenticator.login')) {
            $middlewareQueue->add(OneTimePasswordAuthenticatorMiddleware::class);
        }

        $middlewareQueue = $this->addAuthorizationMiddleware($middlewareQueue);

        return $middlewareQueue;
    }

    /**
     * Add authorization middleware based on Auth.Authorization
     *
     * @param MiddlewareQueue $middlewareQueue queue of middleware
     * @return MiddlewareQueue
     */
    protected function addAuthorizationMiddleware(MiddlewareQueue $middlewareQueue)
    {
        if (Configure::read('Auth.Authorization.enable') === false) {
            return $middlewareQueue;
        }

        return $loader($middlewareQueue, $this);
    }

    /**
     * Load a service defined in configuration $loaderKey
     *
     * @param ServerRequestInterface $request The request.
     * @param ResponseInterface $response The response.
     * @param string $loaderKey service loader key
     *
     * @return mixed
     */
    protected function loadService(ServerRequestInterface $request, ResponseInterface $response, $loaderKey)
    {
        $serviceLoader = $this->getLoader($loaderKey);

        return $serviceLoader($request, $response);
    }

    /**
     * Get the loader callable
     *
     * @param string $loaderKey loader configuration key
     * @return callable
     */
    protected function getLoader($loaderKey)
    {
        $serviceLoader = Configure::read($loaderKey);
        if (is_string($serviceLoader)) {
            $serviceLoader = new $serviceLoader();
        }

        return $serviceLoader;
    }
}
