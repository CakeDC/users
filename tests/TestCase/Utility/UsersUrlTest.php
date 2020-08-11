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

namespace CakeDC\Users\Test\TestCase\Utility;

use Cake\Core\Configure;
use Cake\Http\ServerRequestFactory;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Utility\UsersUrl;
use Zend\Diactoros\Uri;

class UsersUrlTest extends TestCase
{
    /**
     * Data provider for test testActionRoute
     *
     * @return array
     */
    public function dataProviderActionRouteParams()
    {
        return [
            ['verify', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'verify'], null],
            ['linkSocial', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'linkSocial'], null],
            ['callbackLinkSocial', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'callbackLinkSocial'], null],
            ['socialLogin', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'socialLogin'], null],
            ['login', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login'], null],
            ['logout', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'logout'], null],
            ['getUsersTable', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'getUsersTable'], null],
            ['setUsersTable', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'setUsersTable'], null],
            ['profile', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'profile'], null],
            ['validateReCaptcha', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'validateReCaptcha'], null],
            ['register', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'register'], null],
            ['validateEmail', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'validateEmail'], null],
            ['changePassword', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'changePassword'], null],
            ['resetPassword', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'resetPassword'], null],
            ['requestResetPassword', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'requestResetPassword'], null],
            ['resetOneTimePasswordAuthenticator', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'resetOneTimePasswordAuthenticator'], null],
            ['validate', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'validate'], null],
            ['resendTokenValidation', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'resendTokenValidation'], null],
            ['index', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'index'], null],
            ['view', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'view'], null],
            ['add', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'add'], null],
            ['edit', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'edit'], null],
            ['delete', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'delete'], null],
            ['socialEmail', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'socialEmail'], null],

            ['verify', ['controller' => 'Users', 'action' => 'verify'], 'Users'],
            ['linkSocial', ['controller' => 'Users', 'action' => 'linkSocial'], 'Users'],
            ['callbackLinkSocial', ['controller' => 'Users', 'action' => 'callbackLinkSocial'], 'Users'],
            ['socialLogin', ['controller' => 'Users', 'action' => 'socialLogin'], 'Users'],
            ['login', ['controller' => 'Users', 'action' => 'login'], 'Users'],
            ['logout', ['controller' => 'Users', 'action' => 'logout'], 'Users'],
            ['getUsersTable', ['controller' => 'Users', 'action' => 'getUsersTable'], 'Users'],
            ['setUsersTable', ['controller' => 'Users', 'action' => 'setUsersTable'], 'Users'],
            ['profile', ['controller' => 'Users', 'action' => 'profile'], 'Users'],
            ['validateReCaptcha', ['controller' => 'Users', 'action' => 'validateReCaptcha'], 'Users'],
            ['register', ['controller' => 'Users', 'action' => 'register'], 'Users'],
            ['validateEmail', ['controller' => 'Users', 'action' => 'validateEmail'], 'Users'],
            ['changePassword', ['controller' => 'Users', 'action' => 'changePassword'], 'Users'],
            ['resetPassword', ['controller' => 'Users', 'action' => 'resetPassword'], 'Users'],
            ['requestResetPassword', ['controller' => 'Users', 'action' => 'requestResetPassword'], 'Users'],
            ['resetOneTimePasswordAuthenticator', ['controller' => 'Users', 'action' => 'resetOneTimePasswordAuthenticator'], 'Users'],
            ['validate', ['controller' => 'Users', 'action' => 'validate'], 'Users'],
            ['resendTokenValidation', ['controller' => 'Users', 'action' => 'resendTokenValidation'], 'Users'],
            ['index', ['controller' => 'Users', 'action' => 'index'], 'Users'],
            ['view', ['controller' => 'Users', 'action' => 'view'], 'Users'],
            ['add', ['controller' => 'Users', 'action' => 'add'], 'Users'],
            ['edit', ['controller' => 'Users', 'action' => 'edit'], 'Users'],
            ['delete', ['controller' => 'Users', 'action' => 'delete'], 'Users'],
            ['socialEmail', ['controller' => 'Users', 'action' => 'socialEmail'], 'Users'],

            ['verify', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'verify'], 'Admin/Users'],
            ['linkSocial', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'linkSocial'], 'Admin/Users'],
            ['callbackLinkSocial', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'callbackLinkSocial'], 'Admin/Users'],
            ['socialLogin', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'socialLogin'], 'Admin/Users'],
            ['login', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'login'], 'Admin/Users'],
            ['logout', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'logout'], 'Admin/Users'],
            ['getUsersTable', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'getUsersTable'], 'Admin/Users'],
            ['setUsersTable', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'setUsersTable'], 'Admin/Users'],
            ['profile', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'profile'], 'Admin/Users'],
            ['validateReCaptcha', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'validateReCaptcha'], 'Admin/Users'],
            ['register', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'register'], 'Admin/Users'],
            ['validateEmail', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'validateEmail'], 'Admin/Users'],
            ['changePassword', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'changePassword'], 'Admin/Users'],
            ['resetPassword', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'resetPassword'], 'Admin/Users'],
            ['requestResetPassword', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'requestResetPassword'], 'Admin/Users'],
            ['resetOneTimePasswordAuthenticator', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'resetOneTimePasswordAuthenticator'], 'Admin/Users'],
            ['validate', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'validate'], 'Admin/Users'],
            ['resendTokenValidation', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'resendTokenValidation'], 'Admin/Users'],
            ['index', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'index'], 'Admin/Users'],
            ['view', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'view'], 'Admin/Users'],
            ['add', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'add'], 'Admin/Users'],
            ['edit', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'edit'], 'Admin/Users'],
            ['delete', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'delete'], 'Admin/Users'],
            ['socialEmail', ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'socialEmail'], 'Admin/Users'],
        ];
    }

    /**
     * Test actionRouteParams method
     *
     * @dataProvider dataProviderActionRouteParams
     * @param string $action user action.
     * @param array $expected expected url
     * @param string $controller controller name for users, optional
     * @return void
     */
    public function testActionRouteParams($action, $expected, $controller = null)
    {
        Configure::write('Users.controller', $controller);
        $actual = UsersUrl::actionRouteParams($action);
        $this->assertSame($expected, $actual);
    }

    /**
     * Data provider for test testActionParams
     *
     * @return array
     */
    public function dataProviderActionParams()
    {
        return [
            ['verify', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'verify'], null],
            ['linkSocial', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'linkSocial'], null],
            ['callbackLinkSocial', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'callbackLinkSocial'], null],
            ['socialLogin', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'socialLogin'], null],
            ['login', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login'], null],
            ['logout', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'logout'], null],
            ['getUsersTable', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'getUsersTable'], null],
            ['setUsersTable', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'setUsersTable'], null],
            ['profile', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'profile'], null],
            ['validateReCaptcha', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'validateReCaptcha'], null],
            ['register', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'register'], null],
            ['validateEmail', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'validateEmail'], null],
            ['changePassword', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'changePassword'], null],
            ['resetPassword', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'resetPassword'], null],
            ['requestResetPassword', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'requestResetPassword'], null],
            ['resetOneTimePasswordAuthenticator', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'resetOneTimePasswordAuthenticator'], null],
            ['validate', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'validate'], null],
            ['resendTokenValidation', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'resendTokenValidation'], null],
            ['index', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'index'], null],
            ['view', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'view'], null],
            ['add', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'add'], null],
            ['edit', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'edit'], null],
            ['delete', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'delete'], null],
            ['socialEmail', ['prefix' => null, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'socialEmail'], null],

            ['verify', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'verify'], 'Users'],
            ['linkSocial', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'linkSocial'], 'Users'],
            ['callbackLinkSocial', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'callbackLinkSocial'], 'Users'],
            ['socialLogin', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'socialLogin'], 'Users'],
            ['login', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'login'], 'Users'],
            ['logout', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'logout'], 'Users'],
            ['getUsersTable', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'getUsersTable'], 'Users'],
            ['setUsersTable', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'setUsersTable'], 'Users'],
            ['profile', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'profile'], 'Users'],
            ['validateReCaptcha', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'validateReCaptcha'], 'Users'],
            ['register', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'register'], 'Users'],
            ['validateEmail', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'validateEmail'], 'Users'],
            ['changePassword', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'changePassword'], 'Users'],
            ['resetPassword', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'resetPassword'], 'Users'],
            ['requestResetPassword', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'requestResetPassword'], 'Users'],
            ['resetOneTimePasswordAuthenticator', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'resetOneTimePasswordAuthenticator'], 'Users'],
            ['validate', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'validate'], 'Users'],
            ['resendTokenValidation', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'resendTokenValidation'], 'Users'],
            ['index', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'index'], 'Users'],
            ['view', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'view'], 'Users'],
            ['add', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'add'], 'Users'],
            ['edit', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'edit'], 'Users'],
            ['delete', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'delete'], 'Users'],
            ['socialEmail', ['prefix' => null, 'plugin' => null, 'controller' => 'Users', 'action' => 'socialEmail'], 'Users'],

            ['verify', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'verify'], 'Admin/Users'],
            ['linkSocial', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'linkSocial'], 'Admin/Users'],
            ['callbackLinkSocial', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'callbackLinkSocial'], 'Admin/Users'],
            ['socialLogin', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'socialLogin'], 'Admin/Users'],
            ['login', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'login'], 'Admin/Users'],
            ['logout', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'logout'], 'Admin/Users'],
            ['getUsersTable', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'getUsersTable'], 'Admin/Users'],
            ['setUsersTable', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'setUsersTable'], 'Admin/Users'],
            ['profile', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'profile'], 'Admin/Users'],
            ['validateReCaptcha', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'validateReCaptcha'], 'Admin/Users'],
            ['register', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'register'], 'Admin/Users'],
            ['validateEmail', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'validateEmail'], 'Admin/Users'],
            ['changePassword', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'changePassword'], 'Admin/Users'],
            ['resetPassword', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'resetPassword'], 'Admin/Users'],
            ['requestResetPassword', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'requestResetPassword'], 'Admin/Users'],
            ['resetOneTimePasswordAuthenticator', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'resetOneTimePasswordAuthenticator'], 'Admin/Users'],
            ['validate', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'validate'], 'Admin/Users'],
            ['resendTokenValidation', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'resendTokenValidation'], 'Admin/Users'],
            ['index', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'index'], 'Admin/Users'],
            ['view', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'view'], 'Admin/Users'],
            ['add', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'add'], 'Admin/Users'],
            ['edit', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'edit'], 'Admin/Users'],
            ['delete', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'delete'], 'Admin/Users'],
            ['socialEmail', ['prefix' => 'Admin', 'plugin' => null, 'controller' => 'Users', 'action' => 'socialEmail'], 'Admin/Users'],
        ];
    }

    /**
     * Test actionParams method
     *
     * @dataProvider dataProviderActionParams
     * @param string $action user action.
     * @param array $expected expected url
     * @param string $controller controller name for users, optional
     * @return void
     */
    public function testActionParams($action, $expected, $controller = null)
    {
        Configure::write('Users.controller', $controller);
        $actual = UsersUrl::actionParams($action);
        $this->assertSame($expected, $actual);
    }

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
            ['socialEmail', ['prefix' => false, 'plugin' => false, 'controller' => 'Users', 'action' => 'socialEmail'], 'Users'],

            ['verify', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'verify'], 'Admin/Users'],
            ['linkSocial', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'linkSocial'], 'Admin/Users'],
            ['callbackLinkSocial', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'callbackLinkSocial'], 'Admin/Users'],
            ['socialLogin', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'socialLogin'], 'Admin/Users'],
            ['login', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'login'], 'Admin/Users'],
            ['logout', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'logout'], 'Admin/Users'],
            ['getUsersTable', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'getUsersTable'], 'Admin/Users'],
            ['setUsersTable', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'setUsersTable'], 'Admin/Users'],
            ['profile', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'profile'], 'Admin/Users'],
            ['validateReCaptcha', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'validateReCaptcha'], 'Admin/Users'],
            ['register', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'register'], 'Admin/Users'],
            ['validateEmail', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'validateEmail'], 'Admin/Users'],
            ['changePassword', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'changePassword'], 'Admin/Users'],
            ['resetPassword', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'resetPassword'], 'Admin/Users'],
            ['requestResetPassword', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'requestResetPassword'], 'Admin/Users'],
            ['resetOneTimePasswordAuthenticator', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'resetOneTimePasswordAuthenticator'], 'Admin/Users'],
            ['validate', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'validate'], 'Admin/Users'],
            ['resendTokenValidation', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'resendTokenValidation'], 'Admin/Users'],
            ['index', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'index'], 'Admin/Users'],
            ['view', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'view'], 'Admin/Users'],
            ['add', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'add'], 'Admin/Users'],
            ['edit', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'edit'], 'Admin/Users'],
            ['delete', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'delete'], 'Admin/Users'],
            ['socialEmail', ['prefix' => 'Admin', 'plugin' => false, 'controller' => 'Users', 'action' => 'socialEmail'], 'Admin/Users'],
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
        Configure::write('Users.controller', $controller);
        $actual = UsersUrl::actionUrl($action);
        $this->assertSame($expected, $actual);
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
                true,
            ],
            [
                'socialLogin',
                ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'socialLogin', 'provider' => 'facebook'],
                'CakeDC/Users.Users',
                true,
            ],
            [
                'socialLogin',
                ['plugin' => null, 'controller' => 'Users', 'action' => 'socialLogin', 'provider' => 'facebook'],
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
                ['plugin' => null, 'controller' => 'Users', 'action' => 'socialLogin', 'provider' => 'facebook'],
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
     * @dataProvider dataProviderCheckActionOnRequest
     * @return void
     */
    public function testCheckActionOnRequest($action, $params, $controller, $expected)
    {
        Configure::write('Users.controller', $controller);

        $uri = new Uri('/auth/facebook');
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withUri($uri);
        $request = $request->withAttribute('params', $params);
        $actual = UsersUrl::checkActionOnRequest($action, $request);
        $this->assertSame($expected, $actual);
    }
}
