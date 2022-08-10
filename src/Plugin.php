<?php
declare(strict_types=1);

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
use Authorization\AuthorizationServiceInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Http\MiddlewareQueue;
use Psr\Http\Message\ServerRequestInterface;

class Plugin extends BasePlugin implements AuthenticationServiceProviderInterface, AuthorizationServiceProviderInterface
{
    /**
     * Plugin name.
     *
     * @var string
     */
    protected $name = 'CakeDC/Users';
    public const EVENT_BEFORE_LOGIN = 'Users.Authentication.beforeLogin';
    public const EVENT_AFTER_LOGIN = 'Users.Authentication.afterLogin';
    public const EVENT_AFTER_LOGIN_FAILURE = 'Users.Authentication.afterLoginFailure';
    public const EVENT_BEFORE_LOGOUT = 'Users.Authentication.beforeLogout';
    public const EVENT_AFTER_LOGOUT = 'Users.Authentication.afterLogout';

    public const EVENT_BEFORE_REGISTER = 'Users.Global.beforeRegister';
    public const EVENT_AFTER_REGISTER = 'Users.Global.afterRegister';
    public const EVENT_AFTER_CHANGE_PASSWORD = 'Users.Global.afterResetPassword';
    public const EVENT_BEFORE_SOCIAL_LOGIN_USER_CREATE = 'Users.Global.beforeSocialLoginUserCreate';
    public const EVENT_BEFORE_SOCIAL_LOGIN_REDIRECT = 'Users.Global.beforeSocialLoginRedirect';
    public const EVENT_SOCIAL_LOGIN_EXISTING_ACCOUNT = 'Users.Global.socialLoginExistingAccount';
    public const EVENT_ON_EXPIRED_TOKEN = 'Users.Global.onExpiredToken';
    public const EVENT_AFTER_RESEND_TOKEN_VALIDATION = 'Users.Global.afterResendTokenValidation';
    public const EVENT_AFTER_EMAIL_TOKEN_VALIDATION = 'Users.Global.afterEmailTokenValidation';

    /**
     * Returns an authentication service instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request
     * @return \Authentication\AuthenticationServiceInterface
     */
    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        $key = 'Auth.Authentication.serviceLoader';

        return $this->loadService($request, $key);
    }

    /**
     * @inheritDoc
     */
    public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
    {
        $key = 'Auth.Authorization.serviceLoader';

        return $this->loadService($request, $key);
    }

    /**
     * @inheritDoc
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $loader = $this->getLoader('Users.middlewareQueueLoader');

        return $loader($middlewareQueue, $this);
    }

    /**
     * Load a service defined in configuration $loaderKey
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param string $loaderKey service loader key
     * @return mixed
     */
    protected function loadService(ServerRequestInterface $request, $loaderKey)
    {
        $serviceLoader = $this->getLoader($loaderKey);

        return $serviceLoader($request);
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
