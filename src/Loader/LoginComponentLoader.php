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
namespace CakeDC\Users\Loader;

use Cake\Core\Configure;
use Cake\Utility\Hash;

class LoginComponentLoader
{
    /**
     * Load the login component for form login
     *
     * @param \Cake\Controller\Controller $controller Target controller
     * @return \CakeDC\Users\Controller\Component\LoginComponent|\Cake\Controller\Component
     * @throws \Exception
     */
    public static function forForm($controller)
    {
        $config = [
            'component' => 'CakeDC/Users.Login',
            'defaultMessage' => __d('cake_d_c/users', 'Username or password is incorrect'),
            'messages' => [
                'FAILURE_INVALID_RECAPTCHA' => __d('cake_d_c/users', 'Invalid reCaptcha'),
            ],
            'targetAuthenticator' => 'CakeDC\Auth\Authenticator\FormAuthenticator',
        ];

        return self::createComponent($controller, 'Auth.FormLoginFailure', $config);
    }

    /**
     * Load the login component for social login
     *
     * @param \Cake\Controller\Controller $controller Target controller
     * @return \CakeDC\Users\Controller\Component\LoginComponent|\Cake\Controller\Component
     * @throws \Exception
     */
    public static function forSocial($controller)
    {
        $config = [
            'component' => 'CakeDC/Users.Login',
            'defaultMessage' => __d('cake_d_c/users', 'Could not proceed with social account. Please try again'),
            'messages' => [
                'FAILURE_USER_NOT_ACTIVE' => __d(
                    'cake_d_c/users',
                    'Your user has not been validated yet. Please check your inbox for instructions'
                ),
                'FAILURE_ACCOUNT_NOT_ACTIVE' => __d(
                    'cake_d_c/users',
                    'Your social account has not been validated yet. Please check your inbox for instructions'
                ),
            ],
            'targetAuthenticator' => 'CakeDC\Users\Authenticator\SocialAuthenticator',
        ];

        return self::createComponent($controller, 'Auth.SocialLoginFailure', $config);
    }

    /**
     * Create the component using base $config and the one from $key
     *
     * @param \Cake\Controller\Controller $controller Target controller
     * @param string $key configuration key
     * @param array $config base configuration
     * @return \CakeDC\Users\Controller\Component\LoginComponent|\Cake\Controller\Component
     * @throws \Exception
     */
    protected static function createComponent($controller, $key, array $config)
    {
        $custom = (array)Configure::read($key);
        $config = Hash::merge($config, $custom);

        return $controller->loadComponent($config['component'], $config);
    }
}
