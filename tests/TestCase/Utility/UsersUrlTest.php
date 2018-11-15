<?php
/**
 * Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Utility;

use Cake\Core\Configure;
use Cake\Http\ServerRequestFactory;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Utility\UsersUrl;
use Zend\Diactoros\Uri;

class UsersUrlTest extends TestCase
{

    /**
     * Data provider for test testActionUrl
     *
     * @return array
     */
    public function dataProviderActionUrl()
    {
        return [
            ['verify', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'verify'], null],
            ['linkSocial', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'linkSocial'], null],
            ['callbackLinkSocial', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'callbackLinkSocial'], null],
            ['socialLogin', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'socialLogin'], null],
            ['login', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login'], null],
            ['logout', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'logout'], null],
            ['getUsersTable', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'getUsersTable'], null],
            ['setUsersTable', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'setUsersTable'], null],
            ['profile', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'profile'], null],
            ['validateReCaptcha', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'validateReCaptcha'], null],
            ['register', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'register'], null],
            ['validateEmail', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'validateEmail'], null],
            ['changePassword', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'changePassword'], null],
            ['resetPassword', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'resetPassword'], null],
            ['requestResetPassword', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'requestResetPassword'], null],
            ['resetOneTimePasswordAuthenticator', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'resetOneTimePasswordAuthenticator'], null],
            ['validate', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'validate'], null],
            ['resendTokenValidation', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'resendTokenValidation'], null],
            ['index', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'index'], null],
            ['view', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'view'], null],
            ['add', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'add'], null],
            ['edit', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'edit'], null],
            ['delete', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'delete'], null],
            ['socialEmail', ['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'socialEmail'], null],

            ['verify', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'verify'], 'Users'],
            ['linkSocial', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'linkSocial'], 'Users'],
            ['callbackLinkSocial', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'callbackLinkSocial'], 'Users'],
            ['socialLogin', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'socialLogin'], 'Users'],
            ['login', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'login'], 'Users'],
            ['logout', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'logout'], 'Users'],
            ['getUsersTable', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'getUsersTable'], 'Users'],
            ['setUsersTable', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'setUsersTable'], 'Users'],
            ['profile', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'profile'], 'Users'],
            ['validateReCaptcha', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'validateReCaptcha'], 'Users'],
            ['register', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'register'], 'Users'],
            ['validateEmail', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'validateEmail'], 'Users'],
            ['changePassword', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'changePassword'], 'Users'],
            ['resetPassword', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'resetPassword'], 'Users'],
            ['requestResetPassword', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'requestResetPassword'], 'Users'],
            ['resetOneTimePasswordAuthenticator', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'resetOneTimePasswordAuthenticator'], 'Users'],
            ['validate', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'validate'], 'Users'],
            ['resendTokenValidation', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'resendTokenValidation'], 'Users'],
            ['index', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'index'], 'Users'],
            ['view', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'view'], 'Users'],
            ['add', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'add'], 'Users'],
            ['edit', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'edit'], 'Users'],
            ['delete', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'delete'], 'Users'],
            ['socialEmail', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'socialEmail'], 'Users']
        ];
    }

    /**
     * Test actionUrl method
     *
     * @dataProvider dataProviderActionUrl
     * @param string $action user action.
     * @param array $expected expected url
     * @param string $controller controller name for users, optional
     * @return void
     */
    public function testActionUrl($action, $expected, $controller = null)
    {
        $UsersUrl = new UsersUrl();
        Configure::write('Users.controller', $controller);
        $actual = $UsersUrl->actionUrl($action);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Data provider for testCheckActionOnRequest
     *
     * @return array
     */
    public function dataProviderCheckActionOnRequest()
    {
        return [
            [
                'socialLogin',
                ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'socialLogin', 'provider' => 'facebook'],
                null,
                true
            ],
            [
                'socialLogin',
                ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'socialLogin', 'provider' => 'facebook'],
                'CakeDC/Users.Users',
                true,
            ],
            [
                'socialLogin',
                ['plugin' => false, 'controller' => 'Users', 'action' => 'socialLogin', 'provider' => 'facebook'],
                'CakeDC/Users.Users',
                false,
            ],
            [
                'login',
                ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'socialLogin', 'provider' => 'facebook'],
                'CakeDC/Users.Users',
                false,
            ],
            [
                'socialLogin',
                ['plugin' => false, 'controller' => 'Users', 'action' => 'socialLogin', 'provider' => 'facebook'],
                'Users',
                true,
            ],
        ];
    }

    /**
     * Test checkActionOnRequest method
     *
     * @param string $action user action
     * @param array $params request params
     * @param string $controller users controller
     * @param bool $expected result expected
     *
     * @dataProvider dataProviderCheckActionOnRequest
     * @return void
     */
    public function testCheckActionOnRequest($action, $params, $controller, $expected)
    {
        $UsersUrl = new UsersUrl();
        Configure::write('Users.controller', $controller);

        $uri = new Uri('/auth/facebook');
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withUri($uri);
        $request = $request->withAttribute('params', $params);
        $actual = $UsersUrl->checkActionOnRequest($action, $request);
        $this->assertSame($expected, $actual);
    }
}
