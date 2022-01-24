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

namespace CakeDC\Users\Test\TestCase\Controller\Traits\Integration;

use Cake\Core\Configure;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class LoginTraitIntegrationTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.Users',
    ];

    /**
     * Sets up the session as a logged in user for an user with id $id
     *
     * @param $id
     * @return void
     */
    public function loginAsUserId($id)
    {
        $user = TableRegistry::getTableLocator()
            ->get('CakeDC/Users.Users')
            ->get($id);

        $this->session(['Auth' => $user]);
    }

    /**
     * Test login action with get request
     *
     * @return void
     */
    public function testRedirectToLogin()
    {
        $this->enableRetainFlashMessages();
        $this->get('/pages/home');
        $this->assertRedirectContains('/login?redirect=http%3A%2F%2Flocalhost%2Fpages%2Fhome');
        $this->assertFlashMessage('You are not authorized to access that location.');
    }

    /**
     * Test login action with get request
     *
     * @return void
     */
    public function testLoginGetRequestNoSocialLogin()
    {
        EventManager::instance()->on('TestApp.afterPluginBootstrap', function () {
            Configure::write(['Users.Social.login' => false]);
        });

        $this->get('/login');
        $this->assertResponseOk();
        $this->assertResponseNotContains('Username or password is incorrect');
        $this->assertResponseContains('<form method="post" accept-charset="utf-8" action="/login">');
        $this->assertResponseContains('<legend>Please enter your username and password</legend>');
        $this->assertResponseContains('<input type="text" name="username" required="required" id="username" aria-required="true"/>');
        $this->assertResponseContains('<input type="password" name="password" required="required" id="password" aria-required="true"/>');
        $this->assertResponseContains('<input type="checkbox" name="remember_me" value="1" checked="checked" id="remember-me">');
        $this->assertResponseContains('<button type="submit">Login</button>');
        $this->assertResponseContains('<a href="/register">Register</a>');
        $this->assertResponseContains('<a href="/users/request-reset-password">Reset Password</a>');

        $this->assertResponseNotContains('auth/facebook');
        $this->assertResponseNotContains('auth/twitter');
        $this->assertResponseNotContains('auth/google');
        $this->assertResponseNotContains('auth/cognito');
        $this->assertResponseNotContains('auth/amazon');
    }

    /**
     * Test login action with get request
     *
     * @return void
     */
    public function testLoginGetRequest()
    {
        $this->get('/login');
        $this->assertResponseOk();
        $this->assertResponseNotContains('Username or password is incorrect');
        $this->assertResponseContains('<form method="post" accept-charset="utf-8" action="/login">');
        $this->assertResponseContains('<legend>Please enter your username and password</legend>');
        $this->assertResponseContains('<input type="text" name="username" required="required" id="username" aria-required="true"/>');
        $this->assertResponseContains('<input type="password" name="password" required="required" id="password" aria-required="true"/>');
        $this->assertResponseContains('<input type="checkbox" name="remember_me" value="1" checked="checked" id="remember-me">');
        $this->assertResponseContains('<button type="submit">Login</button>');
        $this->assertResponseContains('<a href="/register">Register</a>');
        $this->assertResponseContains('<a href="/users/request-reset-password">Reset Password</a>');

        $this->assertResponseContains('<a href="/auth/facebook" class="btn btn-social btn-facebook"><i class="fa fa-facebook"></i>Sign in with Facebook</a>');
        $this->assertResponseContains('<a href="/auth/twitter" class="btn btn-social btn-twitter"><i class="fa fa-twitter"></i>Sign in with Twitter</a>');
        $this->assertResponseContains('<a href="/auth/google" class="btn btn-social btn-google"><i class="fa fa-google"></i>Sign in with Google</a>');
        $this->assertResponseNotContains('/auth/cognito');
        $this->assertResponseNotContains('/auth/amazon');
    }

    /**
     * Test login action with post request
     *
     * @return void
     */
    public function testLoginPostRequestInvalidPassword()
    {
        $this->post('/login', [
            'username' => 'user-2',
            'password' => '123456789',
        ]);
        $this->assertResponseOk();
        $this->assertResponseContains('Username or password is incorrect');
        $this->assertResponseContains('<form method="post" accept-charset="utf-8" action="/login">');
        $this->assertResponseContains('<legend>Please enter your username and password</legend>');
        $this->assertResponseContains('<input type="text" name="username" required="required" id="username" aria-required="true" value="user-2"/>');
        $this->assertResponseContains('<input type="password" name="password" required="required" id="password" aria-required="true" value="123456789"/>');
        $this->assertResponseContains('<input type="checkbox" name="remember_me" value="1" checked="checked" id="remember-me">');
        $this->assertResponseContains('<button type="submit">Login</button>');
    }

    /**
     * Test login action with post request
     *
     * @return void
     */
    public function testLoginPostRequestRightPasswordWithBaseRedirectUrl()
    {
        $this->enableRetainFlashMessages();
        $this->post('/login?redirect=http://localhost/articles', [
            'username' => 'user-2',
            'password' => '12345',
        ]);
        $this->assertRedirect('http://localhost/articles');
    }

    /**
     * Test login action with post request
     *
     * @return void
     */
    public function testLoginPostRequestRightPasswordNoBaseRedirectUrl()
    {
        $this->enableRetainFlashMessages();
        $this->post('/login', [
            'username' => 'user-2',
            'password' => '12345',
        ]);
        $this->assertRedirect('/pages/home');
    }

    /**
     * Test login action with post request
     *
     * @return void
     */
    public function testLoginPostRequestRightPasswordWithBaseRedirectUrlButCantAccess()
    {
        $this->enableRetainFlashMessages();
        $this->post('/login?redirect=http://localhost/articles', [
            'username' => 'user-4',
            'password' => '12345',
        ]);
        $this->assertRedirect('/pages/home');
    }

    /**
     * Test login action with post request
     *
     * @return void
     */
    public function testLoginPostRequestRightPasswordIsEnabledOTP()
    {
        EventManager::instance()->on('TestApp.afterPluginBootstrap', function () {
            Configure::write(['OneTimePasswordAuthenticator.login' => true]);
        });
        $this->enableRetainFlashMessages();
        $this->post('/login', [
            'username' => 'user-2',
            'password' => '12345',
        ]);
        $this->assertRedirectContains('/verify');
    }

    /**
     * Test login action with post request
     *
     * @return void
     */
    public function testLoginPostRequestRightPasswordIsEnabledU2f()
    {
        EventManager::instance()->on('TestApp.afterPluginBootstrap', function () {
            Configure::write(['U2f.enabled' => true]);
        });
        $this->enableRetainFlashMessages();
        $this->post('/login', [
            'username' => 'user-2',
            'password' => '12345',
        ]);
        $this->assertRedirectContains('/users/u2f');
    }

    /**
     * Test logout action
     *
     * @return void
     */
    public function testLogout()
    {
        $this->loginAsUserId('00000000-0000-0000-0000-000000000002');
        $this->get('/logout');
        $this->assertRedirect('/login');
    }

    /**
     * Test logout action
     *
     * @return void
     */
    public function testLogoutNoUser()
    {
        $this->get('/logout');
        $this->assertRedirect('/login');
    }
}
