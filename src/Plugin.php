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

use Authentication\AuthenticationServiceProviderInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Plugin extends BasePlugin implements AuthenticationServiceProviderInterface, AuthorizationServiceProviderInterface
{
    const EVENT_AFTER_LOGIN = 'Users.Authentication.afterLogin';
    const EVENT_BEFORE_LOGOUT = 'Users.Authentication.beforeLogout';
    const EVENT_AFTER_LOGOUT = 'Users.Authentication.afterLogout';

    const EVENT_BEFORE_REGISTER = 'Users.Global.beforeRegister';
    const EVENT_AFTER_REGISTER = 'Users.Global.afterRegister';
    const EVENT_AFTER_CHANGE_PASSWORD = 'Users.Global.afterResetPassword';
    const EVENT_BEFORE_SOCIAL_LOGIN_USER_CREATE = 'Users.Global.beforeSocialLoginUserCreate';
    const EVENT_BEFORE_SOCIAL_LOGIN_REDIRECT = 'Users.Global.beforeSocialLoginRedirect';
    const EVENT_SOCIAL_LOGIN_EXISTING_ACCOUNT = 'Users.Global.socialLoginExistingAccount';
    const EVENT_ON_EXPIRED_TOKEN = 'Users.Global.onExpiredToken';
    const EVENT_AFTER_RESEND_TOKEN_VALIDATION = 'Users.Global.afterResendTokenValidation';
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
        $key = 'Auth.Authorization.serviceLoader';

        return $this->loadService($request, $response, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function middleware($middlewareQueue)
    {
        $loader = $this->getLoader('Users.middlewareQueueLoader');

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
