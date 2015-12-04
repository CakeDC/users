<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Controller\Component;

use CakeDC\Users\Exception\BadConfigurationException;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Network\Request;
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
    const EVENT_BEFORE_LOGOUT = 'Users.Component.UsersAuth.beforeLogout';
    const EVENT_AFTER_LOGOUT = 'Users.Component.UsersAuth.afterLogout';

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
        $this->_validateConfig();
        $this->_initAuth();

        if (Configure::read('Users.Social.login')) {
            $this->_loadSocialLogin();
        }
        if (Configure::read('Users.RememberMe.active')) {
            $this->_loadRememberMe();
        }

        $this->_attachPermissionChecker();
    }

    /**
     * Load Social Auth object
     *
     * @return void
     */
    protected function _loadSocialLogin()
    {
        $this->_registry->getController()->Auth->config('authenticate', [
            'CakeDC/Users.Social'
        ], true);
    }

    /**
     * Load RememberMe component and Auth objects
     *
     * @return void
     */
    protected function _loadRememberMe()
    {
        $this->_registry->getController()->loadComponent('CakeDC/Users.RememberMe');
    }

    /**
     * Attach the isUrlAuthorized event to allow using the Auth authorize from the UserHelper
     *
     * @return void
     */
    protected function _attachPermissionChecker()
    {
        $this->_registry->getController()->eventManager()->on(self::EVENT_IS_AUTHORIZED, [], [$this, 'isUrlAuthorized']);
    }

    /**
     * Initialize the AuthComponent and configure allowed actions
     *
     * @return void
     */
    protected function _initAuth()
    {
        if (Configure::read('Users.auth')) {
            //initialize Auth
            $this->_registry->getController()->loadComponent('Auth', Configure::read('Auth'));
        }

        $this->_registry->getController()->Auth->allow([
            'register',
            'validateEmail',
            'resendTokenValidation',
            'login',
            'twitterLogin',
            'socialEmail',
            'resetPassword',
            'requestResetPassword',
            'changePassword',
            'endpoint',
            'authenticated'
        ]);
    }

    /**
     * Check if a given url is authorized
     *
     * @param Event $event event
     *
     * @return bool
     */
    public function isUrlAuthorized(Event $event)
    {
        $user = $this->_registry->getController()->Auth->user();
        if (empty($user)) {
            return false;
        }
        $url = Hash::get((array)$event->data, 'url');
        if (empty($url)) {
            return false;
        }

        if (is_array($url)) {
            $requestUrl = Router::reverse($url);
            $requestParams = Router::parse($requestUrl);
        } else {
            $requestParams = Router::parse($url);
            $requestUrl = $url;
        }
        $request = new Request($requestUrl);
        $request->params = $requestParams;

        $isAuthorized = $this->_registry->getController()->Auth->isAuthorized(null, $request);
        return $isAuthorized;
    }

    /**
     * Validate if the passed configuration makes sense
     *
     * @throws BadConfigurationException
     * @return void
     */
    protected function _validateConfig()
    {
        if (!Configure::read('Users.Email.required') && Configure::read('Users.Email.validate')) {
            $message = __d('Users', 'You can\'t enable email validation workflow if use_email is false');
            throw new BadConfigurationException($message);
        }
    }
}
