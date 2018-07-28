<?php

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
use Cake\Core\Configure;
use Cake\Http\MiddlewareQueue;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use CakeDC\Auth\Middleware\RbacMiddleware;
use CakeDC\Users\Authentication\AuthenticationService as CakeDCAuthenticationService;
use CakeDC\Users\Authenticator\FormAuthenticator;
use CakeDC\Users\Authenticator\GoogleTwoFactorAuthenticator;
use CakeDC\Users\Middleware\GoogleAuthenticatorMiddleware;
use CakeDC\Users\Middleware\SocialAuthMiddleware;
use CakeDC\Users\Middleware\SocialEmailMiddleware;
use CakeDC\Users\Plugin;
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
        Configure::write('Users.GoogleAuthenticator.login', true);
        Configure::write('Auth.Authorization.enable', true);
        Configure::write('Auth.Authorization.loadAuthorizationMiddleware', true);
        Configure::write('Auth.Authorization.loadRbacMiddleware', false);

        $plugin = new Plugin();

        $middleware = new MiddlewareQueue();

        $middleware = $plugin->middleware($middleware);
        $this->assertInstanceOf(SocialAuthMiddleware::class, $middleware->get(0));
        $this->assertInstanceOf(SocialEmailMiddleware::class, $middleware->get(1));
        $this->assertInstanceOf(AuthenticationMiddleware::class, $middleware->get(2));
        $this->assertInstanceOf(GoogleAuthenticatorMiddleware::class, $middleware->get(3));
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
        Configure::write('Users.GoogleAuthenticator.login', true);
        Configure::write('Auth.Authorization.enable', true);
        Configure::write('Auth.Authorization.loadAuthorizationMiddleware', true);
        Configure::write('Auth.Authorization.loadRbacMiddleware', true);

        $plugin = new Plugin();

        $middleware = new MiddlewareQueue();

        $middleware = $plugin->middleware($middleware);
        $this->assertInstanceOf(SocialAuthMiddleware::class, $middleware->get(0));
        $this->assertInstanceOf(SocialEmailMiddleware::class, $middleware->get(1));
        $this->assertInstanceOf(AuthenticationMiddleware::class, $middleware->get(2));
        $this->assertInstanceOf(GoogleAuthenticatorMiddleware::class, $middleware->get(3));
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
        Configure::write('Users.GoogleAuthenticator.login', true);
        Configure::write('Auth.Authorization.enable', true);
        Configure::write('Auth.Authorization.loadAuthorizationMiddleware', false);
        Configure::write('Auth.Authorization.loadRbacMiddleware', true);

        $plugin = new Plugin();

        $middleware = new MiddlewareQueue();

        $middleware = $plugin->middleware($middleware);
        $this->assertInstanceOf(SocialAuthMiddleware::class, $middleware->get(0));
        $this->assertInstanceOf(SocialEmailMiddleware::class, $middleware->get(1));
        $this->assertInstanceOf(AuthenticationMiddleware::class, $middleware->get(2));
        $this->assertInstanceOf(GoogleAuthenticatorMiddleware::class, $middleware->get(3));
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
        Configure::write('Users.GoogleAuthenticator.login', true);
        Configure::write('Auth.Authorization.enable', false);
        Configure::write('Auth.Authorization.loadAuthorizationMiddleware', true);//ignore
        Configure::write('Auth.Authorization.loadRbacMiddleware', true);//ignore

        $plugin = new Plugin();

        $middleware = new MiddlewareQueue();

        $middleware = $plugin->middleware($middleware);
        $this->assertInstanceOf(SocialAuthMiddleware::class, $middleware->get(0));
        $this->assertInstanceOf(SocialEmailMiddleware::class, $middleware->get(1));
        $this->assertInstanceOf(AuthenticationMiddleware::class, $middleware->get(2));
        $this->assertInstanceOf(GoogleAuthenticatorMiddleware::class, $middleware->get(3));
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
        Configure::write('Users.GoogleAuthenticator.login', true);
        Configure::write('Auth.Authorization.enable', true);
        Configure::write('Auth.Authorization.loadAuthorizationMiddleware', true);
        Configure::write('Auth.Authorization.loadRbacMiddleware', false);
        $plugin = new Plugin();

        $middleware = new MiddlewareQueue();

        $middleware = $plugin->middleware($middleware);
        $this->assertInstanceOf(AuthenticationMiddleware::class, $middleware->get(0));
        $this->assertInstanceOf(GoogleAuthenticatorMiddleware::class, $middleware->get(1));
        $this->assertInstanceOf(AuthorizationMiddleware::class, $middleware->get(2));
        $this->assertInstanceOf(RequestAuthorizationMiddleware::class, $middleware->get(3));
    }

    /**
     * testMiddleware
     *
     * @return void
     */
    public function testMiddlewareNotGoogleAuthenticator()
    {
        Configure::write('Users.Social.login', true);
        Configure::write('Users.GoogleAuthenticator.login', false);
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
        Configure::write('Users.GoogleAuthenticator.login', false);
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
    public function testGetAuthenticationService()
    {
        Configure::write('Auth.Authenticators', [
            'Authentication.Session' => [
                'skipGoogleVerify' => true,
                'sessionKey' => 'CustomAuth',
                'fields' => ['username' => 'email'],
                'identify' => true,
            ],
            'CakeDC/Users.Form' => [
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
        Configure::write('Users.GoogleAuthenticator.login', true);

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
                'urlChecker' => 'Authentication.Default',
                'fields' => ['username' => 'email', 'password' => 'alt_password']
            ],
            TokenAuthenticator::class => [
                'header' => null,
                'queryParam' => 'api_key',
                'tokenPrefix' => null,
                'skipGoogleVerify' => true
            ],
            GoogleTwoFactorAuthenticator::class => [
                'loginUrl' => null,
                'urlChecker' => 'Authentication.Default',
                'skipGoogleVerify' => true
            ]
        ];

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
    public function testGetAuthenticationServiceWithouGoogleAuthenticator()
    {
        Configure::write('Auth.Authenticators', [
            'Authentication.Session' => [
                'skipGoogleVerify' => true,
                'sessionKey' => 'CustomAuth',
                'fields' => ['username' => 'email'],
                'identify' => true,
            ],
            'CakeDC/Users.Form' => [
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
        Configure::write('Users.GoogleAuthenticator.login', false);

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
                'urlChecker' => 'Authentication.Default',
                'fields' => ['username' => 'email', 'password' => 'alt_password']
            ],
            TokenAuthenticator::class => [
                'header' => null,
                'queryParam' => 'api_key',
                'tokenPrefix' => null,
                'skipGoogleVerify' => true
            ]
        ];
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
}
