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
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\Http\Runner;
use Cake\Http\ServerRequestFactory;
use Cake\TestSuite\TestCase;
use CakeDC\Auth\Social\Mapper\Facebook;
use CakeDC\Users\Middleware\SocialEmailMiddleware;
use League\OAuth2\Client\Provider\FacebookUser;
use Zend\Diactoros\Uri;

class SocialEmailMiddlewareTest extends TestCase
{
    public $fixtures = [
        'plugin.CakeDC/Users.Users',
        'plugin.CakeDC/Users.SocialAccounts',
    ];

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

        $config = [
            'service' => 'CakeDC\Auth\Social\Service\OAuth2Service',
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
                'prefix' => null,
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

        unset($this->Request);
    }

    /**
     * Test when action with get request
     *
     * @return void
     */
    public function testWithGetRquest()
    {
        $Token = new \League\OAuth2\Client\Token\AccessToken([
            'access_token' => 'test-token',
            'expires' => 1490988496,
        ]);

        $user = new FacebookUser([
            'id' => '1',
            'name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => null,
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
        $user = [
                'token' => $Token,
            ] + $user->toArray();

        $mapper = new Facebook();
        $user = $mapper($user);
        $user['provider'] = 'facebook';
        $user['validated'] = true;
        Configure::write('Users.Email.validate', false);
        $this->Request->getSession()->write(Configure::read('Users.Key.Session.social'), $user);

        $uri = new Uri('/auth/facebook');
        $this->Request = $this->Request->withUri($uri);
        $this->Request = $this->Request->withAttribute('params', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'socialEmail',
        ]);

        $Middleware = new SocialEmailMiddleware();
        $response = new Response();
        $response = $response->withStringBody(__METHOD__ . time());
        $handler = $this->getMockBuilder(Runner::class)
            ->setMethods(['handle'])
            ->getMock();
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($this->Request))
            ->willReturn($response);

        $result = $Middleware->process($this->Request, $handler);
        $this->assertSame($response, $result);
        $this->assertEmpty($this->Request->getSession()->read('Auth'));
        $this->assertEmpty($this->Request->getSession()->read('Users.successSocialLogin'));
    }

    /**
     * Test when action without user
     *
     * @return void
     */
    public function testWithoutUser()
    {
        $uri = new Uri('/auth/facebook');
        $this->Request = $this->Request->withUri($uri);
        $this->Request = $this->Request->withParsedBody([
            'email' => 'example@example.com',
        ]);
        $this->Request = $this->Request->withMethod('POST');
        $this->Request = $this->Request->withAttribute('params', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'socialEmail',
        ]);

        $Middleware = new SocialEmailMiddleware();
        $response = new Response();
        $response = $response->withStringBody(__METHOD__ . time());
        $handler = $this->getMockBuilder(Runner::class)
            ->setMethods(['handle'])
            ->getMock();
        $handler->expects($this->never())
            ->method('handle');

        $this->expectException(NotFoundException::class);
        $Middleware->process($this->Request, $handler);
    }

    /**
     * Test when action with successfull authentication
     *
     * @return void
     */
    public function testWithUser()
    {
        $Token = new \League\OAuth2\Client\Token\AccessToken([
            'access_token' => 'test-token',
            'expires' => 1490988496,
        ]);

        $user = new FacebookUser([
            'id' => '1',
            'name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => null,
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
        $user = [
            'token' => $Token,
        ] + $user->toArray();

        $mapper = new Facebook();
        $user = $mapper($user);
        $user['provider'] = 'facebook';
        $user['validated'] = true;
        Configure::write('Users.Email.validate', false);
        $this->Request->getSession()->write(Configure::read('Users.Key.Session.social'), $user);

        $uri = new Uri('/auth/facebook');
        $this->Request = $this->Request->withUri($uri);
        $this->Request = $this->Request->withParsedBody([
            'email' => 'example@example.com',
        ]);
        $this->Request = $this->Request->withMethod('POST');
        $this->Request = $this->Request->withAttribute('params', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'socialEmail',
        ]);

        $Middleware = new SocialEmailMiddleware();
        $response = new Response();
        $response = $response->withStringBody(__METHOD__ . time());
        $handler = $this->getMockBuilder(Runner::class)
            ->setMethods(['handle'])
            ->getMock();
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($this->Request))
            ->willReturn($response);

        $result = $Middleware->process($this->Request, $handler);
        $this->assertSame($response, $result);

        $actual = $this->Request->getSession()->read(Configure::read('Users.Key.Session.social'));
        $this->assertSame($user, $actual);
    }

    /**
     * Test when action without email
     *
     * @return void
     */
    public function testWithoutEmail()
    {
        $Token = new \League\OAuth2\Client\Token\AccessToken([
            'access_token' => 'test-token',
            'expires' => 1490988496,
        ]);

        $user = new FacebookUser([
            'id' => '1',
            'name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => null,
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
        $user = [
                'token' => $Token,
            ] + $user->toArray();

        $mapper = new Facebook();
        $user = $mapper($user);
        $user['provider'] = 'facebook';
        $user['validated'] = true;
        Configure::write('Users.Email.validate', false);
        $this->Request->getSession()->write(Configure::read('Users.Key.Session.social'), $user);

        $uri = new Uri('/auth/facebook');
        $this->Request = $this->Request->withUri($uri);
        $this->Request = $this->Request->withMethod('POST');
        $this->Request = $this->Request->withAttribute('params', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'socialEmail',
        ]);

        $Middleware = new SocialEmailMiddleware();
        $response = new Response();
        $response = $response->withStringBody(__METHOD__ . time());
        $handler = $this->getMockBuilder(Runner::class)
            ->setMethods(['handle'])
            ->getMock();
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($this->Request))
            ->willReturn($response);

        $result = $Middleware->process($this->Request, $handler);
        $this->assertSame($response, $result);
        $this->assertEmpty($this->Request->getSession()->read('Auth'));
    }

    /**
     * Test when action is not valid for social login
     *
     * @return void
     */
    public function testNotValidAction()
    {
        $Middleware = new SocialEmailMiddleware();
        $response = new Response();
        $response = $response->withStringBody(__METHOD__ . time());
        $handler = $this->getMockBuilder(Runner::class)
            ->setMethods(['handle'])
            ->getMock();
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($this->Request))
            ->willReturn($response);

        $result = $Middleware->process($this->Request, $handler);
        $this->assertSame($response, $result);
    }
}
