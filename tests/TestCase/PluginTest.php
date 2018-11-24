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
use CakeDC\Auth\Authentication\AuthenticationService as CakeDCAuthenticationService;
use CakeDC\Auth\Authenticator\FormAuthenticator;
use CakeDC\Auth\Authenticator\TwoFactorAuthenticator;
use CakeDC\Auth\Middleware\OneTimePasswordAuthenticatorMiddleware;
use CakeDC\Auth\Middleware\RbacMiddleware;
use CakeDC\Users\Middleware\SocialAuthMiddleware;
use CakeDC\Users\Middleware\SocialEmailMiddleware;
use CakeDC\Users\Plugin;
use Cake\Core\Configure;
use Cake\Http\MiddlewareQueue;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Http\ServerRequestFactory;
use Cake\TestSuite\IntegrationTestCase;

/**
 * PluginTest class
 */
class PluginTest extends IntegrationTestCase
{

    /**
     * testMiddleware
     *
     * @return void
     */
    public function testMiddleware()
    {
        Configure::write('Users.Social.login', true);
        Configure::write('Users.OneTimePasswordAuthenticator.login', true);
        Configure::write('Auth.Authorization.enable', true);
        Configure::write('Auth.Authorization.loadAuthorizationMiddleware', true);
        Configure::write('Auth.Authorization.loadRbacMiddleware', false);

        $plugin = new Plugin();

        $middleware = new MiddlewareQueue();

        $middleware = $plugin->middleware($middleware);
        $this->assertInstanceOf(SocialAuthMiddleware::class, $middleware->get(0));
        $this->assertInstanceOf(SocialEmailMiddleware::class, $middleware->get(1));
        $this->assertInstanceOf(AuthenticationMiddleware::class, $middleware->get(2));
        $this->assertInstanceOf(OneTimePasswordAuthenticatorMiddleware::class, $middleware->get(3));
        $this->assertInstanceOf(AuthorizationMiddleware::class, $middleware->get(4));
        $this->assertInstanceOf(RequestAuthorizationMiddleware::class, $middleware->get(5));
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
        Configure::write('Users.OneTimePasswordAuthenticator.login', true);
        Configure::write('Auth.Authorization.enable', true);
        Configure::write('Auth.Authorization.loadAuthorizationMiddleware', true);
        Configure::write('Auth.Authorization.loadRbacMiddleware', true);

        $plugin = new Plugin();

        $middleware = new MiddlewareQueue();

        $middleware = $plugin->middleware($middleware);
        $this->assertInstanceOf(SocialAuthMiddleware::class, $middleware->get(0));
        $this->assertInstanceOf(SocialEmailMiddleware::class, $middleware->get(1));
        $this->assertInstanceOf(AuthenticationMiddleware::class, $middleware->get(2));
        $this->assertInstanceOf(OneTimePasswordAuthenticatorMiddleware::class, $middleware->get(3));
        $this->assertInstanceOf(AuthorizationMiddleware::class, $middleware->get(4));
        $this->assertInstanceOf(RequestAuthorizationMiddleware::class, $middleware->get(5));
        $this->assertInstanceOf(RbacMiddleware::class, $middleware->get(6));
        $this->assertEquals(7, $middleware->count());
    }

    /**
     * testMiddleware
     *
     * @return void
     */
    public function testMiddlewareAuthorizationOnlyRbacMiddleware()
    {
        Configure::write('Users.Social.login', true);
        Configure::write('Users.OneTimePasswordAuthenticator.login', true);
        Configure::write('Auth.Authorization.enable', true);
        Configure::write('Auth.Authorization.loadAuthorizationMiddleware', false);
        Configure::write('Auth.Authorization.loadRbacMiddleware', true);

        $plugin = new Plugin();

        $middleware = new MiddlewareQueue();

        $middleware = $plugin->middleware($middleware);
        $this->assertInstanceOf(SocialAuthMiddleware::class, $middleware->get(0));
        $this->assertInstanceOf(SocialEmailMiddleware::class, $middleware->get(1));
        $this->assertInstanceOf(AuthenticationMiddleware::class, $middleware->get(2));
        $this->assertInstanceOf(OneTimePasswordAuthenticatorMiddleware::class, $middleware->get(3));
        $this->assertInstanceOf(RbacMiddleware::class, $middleware->get(4));
        $this->assertEquals(5, $middleware->count());
    }

    /**
     * testMiddleware without authorization
     *
     * @return void
     */
    public function testMiddlewareWithoutAuhorization()
    {
        Configure::write('Users.Social.login', true);
        Configure::write('Users.OneTimePasswordAuthenticator.login', true);
        Configure::write('Auth.Authorization.enable', false);
        Configure::write('Auth.Authorization.loadAuthorizationMiddleware', true);//ignore
        Configure::write('Auth.Authorization.loadRbacMiddleware', true);//ignore

        $plugin = new Plugin();

        $middleware = new MiddlewareQueue();

        $middleware = $plugin->middleware($middleware);
        $this->assertInstanceOf(SocialAuthMiddleware::class, $middleware->get(0));
        $this->assertInstanceOf(SocialEmailMiddleware::class, $middleware->get(1));
        $this->assertInstanceOf(AuthenticationMiddleware::class, $middleware->get(2));
        $this->assertInstanceOf(OneTimePasswordAuthenticatorMiddleware::class, $middleware->get(3));
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
        Configure::write('Users.OneTimePasswordAuthenticator.login', true);
        Configure::write('Auth.Authorization.enable', true);
        Configure::write('Auth.Authorization.loadAuthorizationMiddleware', true);
        Configure::write('Auth.Authorization.loadRbacMiddleware', false);
        $plugin = new Plugin();

        $middleware = new MiddlewareQueue();

        $middleware = $plugin->middleware($middleware);
        $this->assertInstanceOf(AuthenticationMiddleware::class, $middleware->get(0));
        $this->assertInstanceOf(OneTimePasswordAuthenticatorMiddleware::class, $middleware->get(1));
        $this->assertInstanceOf(AuthorizationMiddleware::class, $middleware->get(2));
        $this->assertInstanceOf(RequestAuthorizationMiddleware::class, $middleware->get(3));
    }

    /**
     * testMiddleware
     *
     * @return void
     */
    public function testMiddlewareNotOneTimePasswordAuthenticator()
    {
        Configure::write('Users.Social.login', true);
        Configure::write('Users.OneTimePasswordAuthenticator.login', false);
        Configure::write('Auth.Authorization.enable', true);
        Configure::write('Auth.Authorization.loadAuthorizationMiddleware', true);
        Configure::write('Auth.Authorization.loadRbacMiddleware', false);
        $plugin = new Plugin();

        $middleware = new MiddlewareQueue();

        $middleware = $plugin->middleware($middleware);
        $this->assertInstanceOf(SocialAuthMiddleware::class, $middleware->get(0));
        $this->assertInstanceOf(SocialEmailMiddleware::class, $middleware->get(1));
        $this->assertInstanceOf(AuthenticationMiddleware::class, $middleware->get(2));
        $this->assertInstanceOf(AuthorizationMiddleware::class, $middleware->get(3));
        $this->assertInstanceOf(RequestAuthorizationMiddleware::class, $middleware->get(4));
    }

    /**
     * testMiddleware
     *
     * @return void
     */
    public function testMiddlewareNotGoogleAuthenticationAndNotSocial()
    {
        Configure::write('Users.Social.login', false);
        Configure::write('Users.OneTimePasswordAuthenticator.login', false);
        Configure::write('Auth.Authorization.enable', true);
        Configure::write('Auth.Authorization.loadAuthorizationMiddleware', true);
        Configure::write('Auth.Authorization.loadRbacMiddleware', false);
        $plugin = new Plugin();

        $middleware = new MiddlewareQueue();

        $middleware = $plugin->middleware($middleware);
        $this->assertInstanceOf(AuthenticationMiddleware::class, $middleware->get(0));
        $this->assertInstanceOf(AuthorizationMiddleware::class, $middleware->get(1));
        $this->assertInstanceOf(RequestAuthorizationMiddleware::class, $middleware->get(2));
    }

    /**
     * testGetAuthenticationService
     *
     * @return void
     */
    public function testGetAuthenticationServiceCallableDefined()
    {
        $request = ServerRequestFactory::fromGlobals();
        $request->withQueryParams(['method' => __METHOD__]);
        $response = new Response(['body' => __METHOD__]);
        $service = new CakeDCAuthenticationService([
            'identifiers' => [
                'Authentication.Password'
            ]
        ]);
        Configure::write('Auth.Authentication.serviceLoader', function ($aRequest, $aResponse) use ($request, $response, $service) {
            $this->assertSame($request, $aRequest);
            $this->assertSame($response, $aResponse);

            return $service;
        });

        $plugin = new Plugin();
        $actualService = $plugin->getAuthenticationService($request, $response);
        $this->assertSame($service, $actualService);
    }
    /**
     * testGetAuthenticationService
     *
     * @return void
     */
    public function testGetAuthenticationService()
    {
        Configure::write('Auth.Authenticators', [
            'Authentication.Session' => [
                'skipGoogleVerify' => true,
                'sessionKey' => 'CustomAuth',
                'fields' => ['username' => 'email'],
                'identify' => true,
            ],
            'CakeDC/Auth.Form' => [
                'loginUrl' => '/login',
                'fields' => ['username' => 'email', 'password' => 'alt_password'],
            ],
            'Authentication.Token' => [
                'skipGoogleVerify' => true,
                'header' => null,
                'queryParam' => 'api_key',
                'tokenPrefix' => null,
            ],
        ]);
        Configure::write('Auth.Identifiers', [
            'Authentication.Password' => [
                'fields' => [
                    'username' => 'email_2',
                    'password' => 'password_2'
                ],
            ],
            'Authentication.Token' => [
                'tokenField' => 'api_token'
            ],
            'Authentication.JwtSubject'
        ]);
        Configure::write('Users.OneTimePasswordAuthenticator.login', true);

        $plugin = new Plugin();
        $service = $plugin->getAuthenticationService(new ServerRequest(), new Response());
        $this->assertInstanceOf(CakeDCAuthenticationService::class, $service);

        /**
         * @var \Authentication\Authenticator\AuthenticatorCollection $authenticators
         */
        $authenticators = $service->authenticators();
        $expected = [
            SessionAuthenticator::class => [
                'fields' => ['username' => 'email'],
                'sessionKey' => 'CustomAuth',
                'identify' => true,
                'identityAttribute' => 'identity',
                'skipGoogleVerify' => true
            ],
            FormAuthenticator::class => [
                'loginUrl' => '/login',
                'keyCheckEnabledRecaptcha' => 'Users.reCaptcha.login',
                'fields' => ['username' => 'email', 'password' => 'alt_password']
            ],
            TokenAuthenticator::class => [
                'header' => null,
                'queryParam' => 'api_key',
                'tokenPrefix' => null,
                'skipGoogleVerify' => true
            ],
            TwoFactorAuthenticator::class => [
                'loginUrl' => null,
                'urlChecker' => 'Authentication.Default',
                'skipGoogleVerify' => true
            ]
        ];
        $actual = [];
        foreach ($authenticators as $key => $value) {
            $actual[get_class($value)] = $value->getConfig();
        }
        $this->assertEquals($expected, $actual);

        /**
         * @var \Authentication\Authenticator\AuthenticatorCollection $authenticators
         */
        $identifiers = $service->identifiers();
        $expected = [
            PasswordIdentifier::class => [
                'fields' => [
                    'username' => 'email_2',
                    'password' => 'password_2'
                ],
                'resolver' => 'Authentication.Orm',
                'passwordHasher' => null
            ],
            TokenIdentifier::class => [
                'tokenField' => 'api_token',
                'dataField' => 'token',
                'resolver' => 'Authentication.Orm'
            ],
            JwtSubjectIdentifier::class => [
                'tokenField' => 'id',
                'dataField' => 'sub',
                'resolver' => 'Authentication.Orm'
            ]
        ];
        $actual = [];
        foreach ($identifiers as $key => $value) {
            $actual[get_class($value)] = $value->getConfig();
        }
        $this->assertEquals($expected, $actual);
    }

    /**
     * testGetAuthenticationService
     *
     * @return void
     */
    public function testGetAuthenticationServiceWithouOneTimePasswordAuthenticator()
    {
        Configure::write('Auth.Authenticators', [
            'Authentication.Session' => [
                'skipGoogleVerify' => true,
                'sessionKey' => 'CustomAuth',
                'fields' => ['username' => 'email'],
                'identify' => true,
            ],
            'CakeDC/Auth.Form' => [
                'loginUrl' => '/login',
                'fields' => ['username' => 'email', 'password' => 'alt_password'],
            ],
            'Authentication.Token' => [
                'skipGoogleVerify' => true,
                'header' => null,
                'queryParam' => 'api_key',
                'tokenPrefix' => null,
            ],
        ]);
        Configure::write('Auth.Identifiers', [
            'Authentication.Password',
            'Authentication.Token' => [
                'tokenField' => 'api_token'
            ],
            'Authentication.JwtSubject'
        ]);
        Configure::write('Users.OneTimePasswordAuthenticator.login', false);

        $plugin = new Plugin();
        $service = $plugin->getAuthenticationService(new ServerRequest(), new Response());
        $this->assertInstanceOf(CakeDCAuthenticationService::class, $service);

        /**
         * @var \Authentication\Authenticator\AuthenticatorCollection $authenticators
         */
        $authenticators = $service->authenticators();
        $expected = [
            SessionAuthenticator::class => [
                'fields' => ['username' => 'email'],
                'sessionKey' => 'CustomAuth',
                'identify' => true,
                'identityAttribute' => 'identity',
                'skipGoogleVerify' => true
            ],
            FormAuthenticator::class => [
                'loginUrl' => '/login',
                'fields' => ['username' => 'email', 'password' => 'alt_password'],
                'keyCheckEnabledRecaptcha' => 'Users.reCaptcha.login'
            ],
            TokenAuthenticator::class => [
                'header' => null,
                'queryParam' => 'api_key',
                'tokenPrefix' => null,
                'skipGoogleVerify' => true
            ]
        ];
        $actual = [];
        foreach ($authenticators as $key => $value) {
            $actual[get_class($value)] = $value->getConfig();
        }
        $this->assertEquals($expected, $actual);
    }

    /**
     * testGetAuthorizationService
     *
     * @return void
     */
    public function testGetAuthorizationService()
    {
        $plugin = new Plugin();
        $service = $plugin->getAuthorizationService(new ServerRequest(), new Response());
        $this->assertInstanceOf(AuthorizationService::class, $service);
    }

    /**
     * testGetAuthorizationService
     *
     * @return void
     */
    public function testGetAuthorizationServiceCallableDefined()
    {
        $request = ServerRequestFactory::fromGlobals();
        $request->withQueryParams(['method' => __METHOD__]);
        $response = new Response(['body' => __METHOD__]);
        $service = new AuthorizationService(new ResolverCollection());
        Configure::write('Auth.Authorization.serviceLoader', function ($aRequest, $aResponse) use ($request, $response, $service) {
            $this->assertSame($request, $aRequest);
            $this->assertSame($response, $aResponse);

            return $service;
        });

        $plugin = new Plugin();
        $actualService = $plugin->getAuthorizationService($request, $response);
        $this->assertSame($service, $actualService);
    }
}
