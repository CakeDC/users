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

namespace CakeDC\Users\Test\TestCase;

use Authentication\Authenticator\SessionAuthenticator;
use Authentication\Authenticator\TokenAuthenticator;
use Authentication\Identifier\JwtSubjectIdentifier;
use Authentication\Identifier\PasswordIdentifier;
use Authentication\Identifier\TokenIdentifier;
use Authentication\Middleware\AuthenticationMiddleware;
use Authorization\AuthorizationService;
use Authorization\Middleware\AuthorizationMiddleware;
use Authorization\Middleware\RequestAuthorizationMiddleware;
use Authorization\Policy\ResolverCollection;
use Cake\Core\Configure;
use Cake\Http\MiddlewareQueue;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Http\ServerRequestFactory;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use CakeDC\Auth\Authentication\AuthenticationService as CakeDCAuthenticationService;
use CakeDC\Auth\Authenticator\FormAuthenticator;
use CakeDC\Auth\Authenticator\TwoFactorAuthenticator;
use CakeDC\Auth\Middleware\TwoFactorMiddleware;
use CakeDC\Users\Middleware\SocialAuthMiddleware;
use CakeDC\Users\Middleware\SocialEmailMiddleware;
use CakeDC\Users\Plugin;

/**
 * PluginTest class
 */
class PluginTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * testMiddleware
     *
     * @return void
     */
    public function testMiddleware()
    {
        Configure::write('Users.Social.login', true);
        Configure::write('OneTimePasswordAuthenticator.login', true);
        Configure::write('Auth.Authorization.enable', true);

        $plugin = new Plugin();

        $middleware = new MiddlewareQueue();

        $middleware = $plugin->middleware($middleware);

        // next two is DoublePassDecoratorMiddleware as they not implements MiddlewareInterface
        $middleware->seek(0);
        $this->assertInstanceOf(SocialAuthMiddleware::class, $middleware->current());
        $middleware->seek(1);
        $this->assertInstanceOf(SocialEmailMiddleware::class, $middleware->current());
        $middleware->seek(2);
        $this->assertInstanceOf(AuthenticationMiddleware::class, $middleware->current());
        $middleware->seek(3);
        $this->assertInstanceOf(TwoFactorMiddleware::class, $middleware->current());
        $middleware->seek(4);
        $this->assertInstanceOf(AuthorizationMiddleware::class, $middleware->current());
        $middleware->seek(5);
        $this->assertInstanceOf(RequestAuthorizationMiddleware::class, $middleware->current());
        $this->assertEquals(6, $middleware->count());
    }

    /**
     * testMiddleware
     *
     * @return void
     */
    public function testMiddlewareAuthorizationMiddlewareAndRbacMiddleware()
    {
        Configure::write('Users.Social.login', true);
        Configure::write('OneTimePasswordAuthenticator.login', true);
        Configure::write('Auth.Authorization.enable', true);

        $plugin = new Plugin();

        $middleware = new MiddlewareQueue();

        $middleware = $plugin->middleware($middleware);
        $middleware->seek(0);
        $this->assertInstanceOf(SocialAuthMiddleware::class, $middleware->current());
        $middleware->seek(1);
        $this->assertInstanceOf(SocialEmailMiddleware::class, $middleware->current());
        $middleware->seek(2);
        $this->assertInstanceOf(AuthenticationMiddleware::class, $middleware->current());
        $middleware->seek(3);
        $this->assertInstanceOf(TwoFactorMiddleware::class, $middleware->current());
        $middleware->seek(4);
        $this->assertInstanceOf(AuthorizationMiddleware::class, $middleware->current());
        $middleware->seek(5);
        $this->assertInstanceOf(RequestAuthorizationMiddleware::class, $middleware->current());
        $this->assertEquals(6, $middleware->count());
    }

    /**
     * testMiddleware without authorization
     *
     * @return void
     */
    public function testMiddlewareWithoutAuhorization()
    {
        Configure::write('Users.Social.login', true);
        Configure::write('OneTimePasswordAuthenticator.login', true);
        Configure::write('Auth.Authorization.enable', false);

        $plugin = new Plugin();

        $middleware = new MiddlewareQueue();

        $middleware = $plugin->middleware($middleware);
        $middleware->seek(0);
        $this->assertInstanceOf(SocialAuthMiddleware::class, $middleware->current());
        $middleware->seek(1);
        $this->assertInstanceOf(SocialEmailMiddleware::class, $middleware->current());
        $middleware->seek(2);
        $this->assertInstanceOf(AuthenticationMiddleware::class, $middleware->current());
        $middleware->seek(3);
        $this->assertInstanceOf(TwoFactorMiddleware::class, $middleware->current());
        $this->assertEquals(4, $middleware->count());
    }

    /**
     * testMiddleware
     *
     * @return void
     */
    public function testMiddlewareNotSocial()
    {
        Configure::write('Users.Social.login', false);
        Configure::write('OneTimePasswordAuthenticator.login', true);
        Configure::write('Auth.Authorization.enable', true);
        $plugin = new Plugin();

        $middleware = new MiddlewareQueue();

        $middleware = $plugin->middleware($middleware);
        $middleware->seek(0);
        $this->assertInstanceOf(AuthenticationMiddleware::class, $middleware->current());
        $middleware->seek(1);
        $this->assertInstanceOf(TwoFactorMiddleware::class, $middleware->current());
        $middleware->seek(2);
        $this->assertInstanceOf(AuthorizationMiddleware::class, $middleware->current());
        $middleware->seek(3);
        $this->assertInstanceOf(RequestAuthorizationMiddleware::class, $middleware->current());
    }

    /**
     * testMiddleware
     *
     * @return void
     */
    public function testMiddlewareNotOneTimePasswordAuthenticator()
    {
        Configure::write('Users.Social.login', true);
        Configure::write('OneTimePasswordAuthenticator.login', false);
        Configure::write('Auth.Authorization.enable', true);
        $plugin = new Plugin();

        $middleware = new MiddlewareQueue();

        $middleware = $plugin->middleware($middleware);
        $middleware->seek(0);
        $this->assertInstanceOf(SocialAuthMiddleware::class, $middleware->current());
        $middleware->seek(1);
        $this->assertInstanceOf(SocialEmailMiddleware::class, $middleware->current());
        $middleware->seek(2);
        $this->assertInstanceOf(AuthenticationMiddleware::class, $middleware->current());
        $middleware->seek(3);
        $this->assertInstanceOf(AuthorizationMiddleware::class, $middleware->current());
        $middleware->seek(4);
        $this->assertInstanceOf(RequestAuthorizationMiddleware::class, $middleware->current());
    }

    /**
     * testMiddleware
     *
     * @return void
     */
    public function testMiddlewareNotGoogleAuthenticationAndNotSocial()
    {
        Configure::write('Users.Social.login', false);
        Configure::write('OneTimePasswordAuthenticator.login', false);
        Configure::write('Auth.Authorization.enable', true);
        $plugin = new Plugin();

        $middleware = new MiddlewareQueue();

        $middleware = $plugin->middleware($middleware);
        $middleware->seek(0);
        $this->assertInstanceOf(AuthenticationMiddleware::class, $middleware->current());
        $middleware->seek(1);
        $this->assertInstanceOf(AuthorizationMiddleware::class, $middleware->current());
        $middleware->seek(2);
        $this->assertInstanceOf(RequestAuthorizationMiddleware::class, $middleware->current());
    }

    /**
     * test bootstrap method
     *
     * @param string $urlConfigKey The url config key.
     * @param array $expectedUrl The expected url value for $urlConfigKey.
     * @dataProvider dataProviderConfigUsersUrls
     * @return void
     */
    public function testBootstrap($urlConfigKey, $expectedUrl)
    {
        $actual = Configure::read($urlConfigKey);
        $this->assertSame($expectedUrl, $actual);
    }

    /**
     * Data provider for users urls
     *
     * @return array
     */
    public function dataProviderConfigUsersUrls()
    {
        $defaultVerifyAction = [
            'prefix' => false,
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'verify',
        ];
        $defaultProfileAction = [
            'prefix' => false,
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'profile',
        ];
        $defaultU2fStartAction = [
            'prefix' => false,
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'u2f',
        ];
        $defaultLoginAction = [
            'prefix' => false,
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'login',
        ];
        $defaultOauthPath = [
            'prefix' => null,
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'socialLogin',
        ];

        return [
            ['Users.Profile.route', $defaultProfileAction],
            ['OneTimePasswordAuthenticator.verifyAction', $defaultVerifyAction],
            ['U2f.startAction', $defaultU2fStartAction],
            ['Auth.AuthenticationComponent.loginAction', $defaultLoginAction],
            ['Auth.AuthenticationComponent.logoutRedirect', $defaultLoginAction],
            ['Auth.AuthenticationComponent.loginRedirect', '/'],
            ['Auth.Authenticators.Form.loginUrl', $defaultLoginAction],
            ['Auth.Authenticators.Cookie.loginUrl', $defaultLoginAction],
            ['Auth.Authenticators.SocialPendingEmail.loginUrl', $defaultLoginAction],
            ['Auth.AuthorizationMiddleware.unauthorizedHandler.url', $defaultLoginAction],
            ['OAuth.path', $defaultOauthPath],
        ];
    }
}
