<?php
/**
 * Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Controller\Component;

use CakeDC\Users\Exception\BadConfigurationException;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Http\ServerRequest;
use Cake\Routing\Exception\MissingRouteException;
use Cake\Routing\Router;
use Cake\Utility\Hash;

class UsersAuthComponent extends Component
{
    const EVENT_IS_AUTHORIZED = 'Users.Component.UsersAuth.isAuthorized';
    const EVENT_BEFORE_LOGIN = 'Users.Component.UsersAuth.beforeLogin';
    const EVENT_AFTER_LOGIN = 'Users.Component.UsersAuth.afterLogin';
    const EVENT_FAILED_SOCIAL_LOGIN = 'Users.Component.UsersAuth.failedSocialLogin';
    const EVENT_AFTER_COOKIE_LOGIN = 'Users.Component.UsersAuth.afterCookieLogin';
    const EVENT_BEFORE_REGISTER = 'Users.Component.UsersAuth.beforeRegister';
    const EVENT_AFTER_REGISTER = 'Users.Component.UsersAuth.afterRegister';
    const EVENT_AFTER_REGISTER_CONFIRM = 'Users.Component.UsersAuth.afterRegister.confirm';
    const EVENT_BEFORE_LOGOUT = 'Users.Component.UsersAuth.beforeLogout';
    const EVENT_AFTER_LOGOUT = 'Users.Component.UsersAuth.afterLogout';
    const EVENT_BEFORE_SOCIAL_LOGIN_USER_CREATE = 'Users.Component.UsersAuth.beforeSocialLoginUserCreate';
    const EVENT_AFTER_CHANGE_PASSWORD = 'Users.Component.UsersAuth.afterResetPassword';
    const EVENT_ON_EXPIRED_TOKEN = 'Users.Component.UsersAuth.onExpiredToken';
    const EVENT_AFTER_RESEND_TOKEN_VALIDATION = 'Users.Component.UsersAuth.afterResendTokenValidation';

    /**
     * Initialize method, setup Auth if not already done passing the $config provided and
     * setup the default table to Users.Users if not provided
     *
     * @param array $config config options
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
    }
}
