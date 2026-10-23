<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace CakeDC\Users\Loader;

use Cake\Controller\Component;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Utility\Hash;
use CakeDC\Auth\Authenticator\FormAuthenticator;
use CakeDC\Users\Authenticator\SocialAuthenticator;
use CakeDC\Users\Controller\Component\LoginComponent;

class LoginComponentLoader
{
    /**
     * Load the login component for form login
     *
     * @param \Cake\Controller\Controller $controller Target controller
     * @return \CakeDC\Users\Controller\Component\LoginComponent|\Cake\Controller\Component
     * @throws \Exception
     */
    public static function forForm(Controller $controller): LoginComponent|Component
    {
        $config = [
            'component' => 'CakeDC/Users.Login',
            'defaultMessage' => __d('cake_d_c/users', 'Username or password is incorrect'),
            'messages' => [
                'FAILURE_INVALID_RECAPTCHA' => __d('cake_d_c/users', 'Invalid reCaptcha'),
            ],
            'targetAuthenticator' => FormAuthenticator::class,
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
    public static function forSocial(Controller $controller): LoginComponent|Component
    {
        $config = [
            'component' => 'CakeDC/Users.Login',
            'defaultMessage' => __d('cake_d_c/users', 'Could not proceed with social account. Please try again'),
            'messages' => [
                'FAILURE_USER_NOT_ACTIVE' => __d(
                    'cake_d_c/users',
                    'Your user has not been validated yet. Please check your inbox for instructions',
                ),
                'FAILURE_ACCOUNT_NOT_ACTIVE' => __d(
                    'cake_d_c/users',
                    'Your social account has not been validated yet. Please check your inbox for instructions',
                ),
            ],
            'targetAuthenticator' => SocialAuthenticator::class,
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
    protected static function createComponent(Controller $controller, string $key, array $config): LoginComponent|Component
    {
        $custom = (array)Configure::read($key);
        $config = Hash::merge($config, $custom);

        return $controller->loadComponent($config['component'], $config);
    }
}
