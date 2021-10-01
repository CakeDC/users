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

use Cake\Core\BasePlugin;
use Cake\Http\MiddlewareQueue;
use CakeDC\Users\Provider\AuthenticationServiceProvider;
use CakeDC\Users\Provider\AuthorizationServiceProvider;
use CakeDC\Users\Provider\ServiceProviderLoaderTrait;

class Plugin extends BasePlugin
{
    use ServiceProviderLoaderTrait;

    /**
     * Plugin name.
     *
     * @var string
     */
    protected $name = 'CakeDC/Users';
    public const EVENT_AFTER_LOGIN = 'Users.Authentication.afterLogin';
    public const EVENT_BEFORE_LOGOUT = 'Users.Authentication.beforeLogout';
    public const EVENT_AFTER_LOGOUT = 'Users.Authentication.afterLogout';
    public const EVENT_FAILED_LOGIN = 'Users.Authentication.failedLogin';

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
     * @inheritDoc
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $loader = $this->getLoader('Users.middlewareQueueLoader');

        return $loader(
            $middlewareQueue,
            new AuthenticationServiceProvider(),
            new AuthorizationServiceProvider()
        );
    }
}
