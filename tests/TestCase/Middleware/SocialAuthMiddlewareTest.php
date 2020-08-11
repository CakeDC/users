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

namespace CakeDC\Users\Test\TestCase\Middleware;

use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Http\ServerRequestFactory;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use CakeDC\Auth\Social\MapUser;
use CakeDC\Auth\Social\Service\OAuth2Service;
use CakeDC\Auth\Social\Service\ServiceFactory;
use CakeDC\Users\Exception\MissingEmailException;
use CakeDC\Users\Exception\SocialAuthenticationException;
use CakeDC\Users\Middleware\SocialAuthMiddleware;
use Doctrine\Instantiator\Exception\UnexpectedValueException;
use League\OAuth2\Client\Provider\FacebookUser;
use TestApp\Http\TestRequestHandler;
use Zend\Diactoros\Uri;

class SocialAuthMiddlewareTest extends TestCase
{
    public $fixtures = [
        'plugin.CakeDC/Users.Users',
        'plugin.CakeDC/Users.SocialAccounts',
    ];

    /**
     * @var \League\OAuth2\Client\Provider\Facebook
     */
    public $Provider;

    /**
     * @var \Cake\Http\ServerRequest
     */
    public $Request;

    /**
     * Setup the test case, backup the static object values so they can be restored.
     * Specifically backs up the contents of Configure and paths in App if they have
     * not already been backed up.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->Provider = $this->getMockBuilder('\League\OAuth2\Client\Provider\Facebook')->setConstructorArgs([
            [
                'graphApiVersion' => 'v2.8',
                'redirectUri' => '/auth/facebook',
                'linkSocialUri' => '/link-social/facebook',
                'callbackLinkSocialUri' => '/callback-link-social/facebook',
                'clientId' => '10003030300303',
                'clientSecret' => 'secretpassword',
            ],
            [],
        ])->setMethods([
            'getAccessToken', 'getState', 'getAuthorizationUrl', 'getResourceOwner',
        ])->getMock();

        $config = [
            'service' => 'CakeDC\Auth\Social\Service\OAuth2Service',
            'className' => $this->Provider,
            'mapper' => 'CakeDC\Auth\Social\Mapper\Facebook',
            'options' => [
                'state' => '__TEST_STATE__',
                'graphApiVersion' => 'v2.8',
                'redirectUri' => '/auth/facebook',
                'linkSocialUri' => '/link-social/facebook',
                'callbackLinkSocialUri' => '/callback-link-social/facebook',
                'clientId' => '10003030300303',
                'clientSecret' => 'secretpassword',
            ],
            'collaborators' => [],
            'signature' => null,
            'mapFields' => [],
            'path' => [
                'plugin' => 'CakeDC/Users',
                'controller' => 'Users',
                'action' => 'socialLogin',
                'prefix' => false,
            ],
        ];
        Configure::write('OAuth.providers.facebook', $config);

        $this->Request = ServerRequestFactory::fromGlobals();
    }

    /**
     * teardown any static object changes and restore them.
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->Provider, $this->Request);
    }

    /**
     * Test when user is on step one
     *
     * @return void
     */
    public function testProceedStepOne()
    {
        $uri = new Uri('/auth/facebook');
        $this->Request = $this->Request->withUri($uri);

        $this->Request = $this->Request->withAttribute('params', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'socialLogin',
            'provider' => 'facebook',
        ]);

        $this->Provider->expects($this->any())
            ->method('getState')
            ->will($this->returnValue('_NEW_STATE_'));

        $this->Provider->expects($this->any())
            ->method('getAuthorizationUrl')
            ->will($this->returnValue('http://facebook.com/redirect/url'));

        $Middleware = new SocialAuthMiddleware();
        $response = new Response();
        $handlerCb = function () use ($response) {
            $this->fail('Should not call $next');
        };

        $handler = new TestRequestHandler($handlerCb);
        /**
         * @var Response $result
         */
        $result = $Middleware->process($this->Request, $handler);
        $this->assertInstanceOf(Response::class, $result);
        if (!$result) {
            $this->fail('No response set, cannot assert location header. ');
        }

        $actual = $this->Request->getSession()->read('oauth2state');
        $expected = '_NEW_STATE_';
        $this->assertEquals($expected, $actual);

        $actual = $result->getHeaderLine('Location');
        $expected = 'http://facebook.com/redirect/url';
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test when user is on get user step
     *
     * @return void
     */
    public function testSuccessfullyAuthenticated()
    {
        $uri = new Uri('/auth/facebook');
        $this->Request = $this->Request->withUri($uri);
        $this->Request = $this->Request->withQueryParams([
            'code' => 'ZPO9972j3092304230',
            'state' => '__TEST_STATE__',
        ]);
        $this->Request = $this->Request->withAttribute('params', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'socialLogin',
            'provider' => 'facebook',
        ]);
        $this->Request->getSession()->write('oauth2state', '__TEST_STATE__');
        $Middleware = new SocialAuthMiddleware();

        $ResponseOriginal = new Response();
        $checked = false;
        $handlerCb = function (ServerRequest $request) use ($ResponseOriginal, &$checked) {
            /**
             * @var OAuth2Service $service
             */
            $service = $request->getAttribute('socialService');
            $this->assertInstanceOf(OAuth2Service::class, $service);
            $this->assertEquals('facebook', $service->getProviderName());
            $this->assertTrue($service->isGetUserStep($request));
            $checked = true;

            return $ResponseOriginal;
        };
        $handler = new TestRequestHandler($handlerCb);
        $response = $Middleware->process($this->Request, $handler);

        $this->assertSame($response, $ResponseOriginal);
        $this->assertTrue($checked);
    }

    /**
     * Data provider for testSocialAuthenticationException
     *
     * @return array
     */
    public function dataProviderSocialAuthenticationException()
    {
        $missingEmail = [
            new MissingEmailException('Missing email'),
            [
                'key' => 'flash',
                'element' => 'Flash/error',
                'params' => [],
                'message' => __d('cake_d_c/users', 'Please enter your email'),
            ],
            '/users/users/social-email',
            true,
        ];
        $unknown = [
            new UnexpectedValueException('User not active'),
            [
                'key' => 'flash',
                'element' => 'Flash/error',
                'params' => [],
                'message' => __d('cake_d_c/users', 'Could not identify your account, please try again'),
            ],
            '/login',
            false,
        ];

        return [
            $missingEmail,
            $unknown,
        ];
    }

    /**
     * Test when has error getting user
     *
     * @param \Exception $previousException previous exception used on SocialAuthenticationException
     * @param array $flash flash that should be on session
     * @param array $location value of location header that should be on request
     * @param bool $keepSocialUser should keed a raw data of social user
     * @dataProvider dataProviderSocialAuthenticationException
     * @return void
     */
    public function testSocialAuthenticationException($previousException, $flash, $location, $keepSocialUser)
    {
        Router::connect('/login', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'login',
            '_ext' => null,
            'prefix' => null,
        ]);
        Router::connect('/users/users/social-email', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'socialEmail',
            '_ext' => null,
            'prefix' => null,
        ]);
        $uri = new Uri('/auth/facebook');
        $this->Request = $this->Request->withUri($uri);
        $this->Request = $this->Request->withQueryParams([
            'code' => 'ZPO9972j3092304230',
            'state' => '__TEST_STATE__',
        ]);
        $this->Request = $this->Request->withAttribute('params', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'socialLogin',
            'provider' => 'facebook',
        ]);
        $this->Request->getSession()->write('oauth2state', '__TEST_STATE__');

        $Middleware = new SocialAuthMiddleware();

        $ResponseOriginal = new Response();
        $checked = false;

        $service = (new ServiceFactory())->createFromProvider('facebook');
        $Token = new \League\OAuth2\Client\Token\AccessToken([
            'access_token' => 'test-token',
            'expires' => 1490988496,
        ]);
        $user = new FacebookUser([
            'id' => '1',
            'name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@gmail.com',
            'hometown' => [
                'id' => '108226049197930',
                'name' => 'Madrid',
            ],
            'picture' => [
                'data' => [
                    'url' => 'https://scontent.xx.fbcdn.net/v/test.jpg',
                    'is_silhouette' => false,
                ],
            ],
            'cover' => [
                'source' => 'https://scontent.xx.fbcdn.net/v/test.jpg',
                'id' => '1',
            ],
            'gender' => 'male',
            'locale' => 'en_US',
            'link' => 'https://www.facebook.com/app_scoped_user_id/1/',
            'timezone' => -5,
            'age_range' => [
                'min' => 21,
            ],
            'bio' => 'I am the best test user in the world.',
            'picture_url' => 'https://scontent.xx.fbcdn.net/v/test.jpg',
            'is_silhouette' => false,
            'cover_photo_url' => 'https://scontent.xx.fbcdn.net/v/test.jpg',
        ]);
        $user = ['token' => $Token] + $user->toArray();
        $mapper = new MapUser();
        $rawData = $mapper($service, $user);
        $handlerCb = function (ServerRequest $request) use ($previousException, $ResponseOriginal, &$checked, $rawData) {
            /**
             * @var OAuth2Service $service
             */
            $service = $request->getAttribute('socialService');
            $this->assertInstanceOf(OAuth2Service::class, $service);
            $this->assertEquals('facebook', $service->getProviderName());
            $this->assertTrue($service->isGetUserStep($request));
            $checked = true;

            throw new SocialAuthenticationException(
                [
                    'rawData' => $rawData,
                ],
                null,
                $previousException
            );
        };
        $handler = new TestRequestHandler($handlerCb);
        /**
         * @var Response $result
         */
        $result = $Middleware->process($this->Request, $handler);

        $this->assertInstanceOf(Response::class, $result);
        $actual = $result->getHeader('Location');
        $expected = [$location];
        $this->assertEquals($expected, $actual);
        $expected = [
            $flash,
        ];
        $actual = $this->Request->getSession()->read('Flash.flash');
        $this->assertEquals($expected, $actual);

        if ($keepSocialUser) {
            $actual = $this->Request->getSession()->read(Configure::read('Users.Key.Session.social'));
            $mapper = new MapUser();
            $expected = $mapper($service, $user);
            $this->assertEquals($expected, $actual);
        }
    }

    /**
     * Test when action is not valid for social login
     *
     * @return void
     */
    public function testNotValidAction()
    {
        $response = new Response();
        $response = $response->withStringBody(__METHOD__ . time());
        $Middleware = new SocialAuthMiddleware();
        $handlerCb = function ($request) use ($response) {
            return $response;
        };

        $handler = new TestRequestHandler($handlerCb);
        /**
         * @var Response $result
         */
        $result = $Middleware->process($this->Request, $handler);
        $this->assertSame($result, $result);
    }
}
