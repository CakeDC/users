<?php
/**
 * Created by PhpStorm.
 * User: usuario
 * Date: 16/04/18
 * Time: 19:27
 */

namespace CakeDC\Users\Test\TestCase\Middleware;

use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequestFactory;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Social\Mapper\Facebook;
use CakeDC\Users\Middleware\SocialAuthMiddleware;
use CakeDC\Users\Model\Entity\User;
use CakeDC\Users\Social\Service\OAuth2Service;
use League\OAuth2\Client\Provider\FacebookUser;
use Zend\Diactoros\Uri;

class SocialAuthMiddlewareTest extends TestCase
{

    public $fixtures = [
        'plugin.CakeDC/Users.users',
        'plugin.CakeDC/Users.social_accounts'
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
    public function setUp()
    {
        parent::setUp();

        $this->Provider = $this->getMockBuilder('\League\OAuth2\Client\Provider\Facebook')->setConstructorArgs([
            [
                'graphApiVersion' => 'v2.8',
                'redirectUri' => '/auth/facebook',
                'linkSocialUri' => '/link-social/facebook',
                'callbackLinkSocialUri' => '/callback-link-social/facebook',
                'clientId' => '10003030300303',
                'clientSecret' => 'secretpassword'
            ],
            []
        ])->setMethods([
            'getAccessToken', 'getState', 'getAuthorizationUrl', 'getResourceOwner'
        ])->getMock();

        $config = [
            'service' => 'CakeDC\Users\Social\Service\OAuth2Service',
            'className' => $this->Provider,
            'mapper' => 'CakeDC\Users\Social\Mapper\Facebook',
            'options' => [
                'state' => '__TEST_STATE__',
                'graphApiVersion' => 'v2.8',
                'redirectUri' => '/auth/facebook',
                'linkSocialUri' => '/link-social/facebook',
                'callbackLinkSocialUri' => '/callback-link-social/facebook',
                'clientId' => '10003030300303',
                'clientSecret' => 'secretpassword'
            ],
            'collaborators' => [],
            'signature' => null,
            'mapFields' => [],
            'path' => [
                'plugin' => 'CakeDC/Users',
                'controller' => 'Users',
                'action' => 'socialLogin',
                'prefix' => null
            ]
        ];
        Configure::write('OAuth.providers.facebook', $config);

        $this->Request = ServerRequestFactory::fromGlobals();
    }

    /**
     * teardown any static object changes and restore them.
     *
     * @return void
     */
    public function tearDown()
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

        $this->Request = $this->Request->withAttribute('params',[
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'socialLogin',
            'provider' => 'facebook'
        ]);

        $this->Provider->expects($this->any())
            ->method('getState')
            ->will($this->returnValue('_NEW_STATE_'));

        $this->Provider->expects($this->any())
            ->method('getAuthorizationUrl')
            ->will($this->returnValue('http://facebook.com/redirect/url'));


        $Middleware = new SocialAuthMiddleware();
        $response = new Response();
        $next = function ($request, $response) {
            $this->fail('Should not call $next');
        };

        $result = $Middleware($this->Request, $response, $next);
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
     * Test when user successfully authenticated
     *
     * @return void
     */
    public function testSuccessfullyAuthenticated()
    {
        $uri = new Uri('/auth/facebook');
        $this->Request = $this->Request->withUri($uri);
        $this->Request = $this->Request->withQueryParams([
            'code' => 'ZPO9972j3092304230',
            'state' => '__TEST_STATE__'
        ]);
        $this->Request = $this->Request->withAttribute('params',[
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'socialLogin',
            'provider' => 'facebook'
        ]);
        $this->Request->getSession()->write('oauth2state','__TEST_STATE__');

        $Token = new \League\OAuth2\Client\Token\AccessToken([
            'access_token' => 'test-token',
            'expires' => 1490988496
        ]);

        $user = new FacebookUser([
            'id' => '1',
            'name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@gmail.com',
            'hometown' => [
                'id' => '108226049197930',
                'name' => 'Madrid'
            ],
            'picture' => [
                'data' => [
                    'url' => 'https://scontent.xx.fbcdn.net/v/test.jpg',
                    'is_silhouette' => false
                ]
            ],
            'cover' => [
                'source' => 'https://scontent.xx.fbcdn.net/v/test.jpg',
                'id' => '1'
            ],
            'gender' => 'male',
            'locale' => 'en_US',
            'link' => 'https://www.facebook.com/app_scoped_user_id/1/',
            'timezone' => -5,
            'age_range' => [
                'min' => 21
            ],
            'bio' => 'I am the best test user in the world.',
            'picture_url' => 'https://scontent.xx.fbcdn.net/v/test.jpg',
            'is_silhouette' => false,
            'cover_photo_url' => 'https://scontent.xx.fbcdn.net/v/test.jpg'
        ]);

        $this->Provider->expects($this->never())
            ->method('getAuthorizationUrl');

        $this->Provider->expects($this->never())
            ->method('getState');

        $this->Provider->expects($this->any())
            ->method('getAccessToken')
            ->with(
                $this->equalTo('authorization_code'),
                $this->equalTo(['code' => 'ZPO9972j3092304230'])
            )
            ->will($this->returnValue($Token));

        $this->Provider->expects($this->any())
            ->method('getResourceOwner')
            ->with(
                $this->equalTo($Token)
            )
            ->will($this->returnValue($user));

        $Middleware = new SocialAuthMiddleware();

        $response = new Response();
        $next = function ($request, $response) {
            return compact('request', 'response');
        };

        $result = $Middleware($this->Request, $response, $next);
        $this->assertEquals(SocialAuthMiddleware::AUTH_SUCCESS, $result['request']->getAttribute('socialAuthStatus'));
        $this->assertNotEmpty($result['request']->getAttribute('socialRawData'));
        $this->assertNotEmpty($result['request']->getAttribute('socialRawData')['id']);
        $this->assertInstanceOf(User::class, $this->Request->getSession()->read('Auth'));
        $this->assertEquals(200, $result['response']->getStatusCode());
    }

    /**
     * Test when has error getting user
     *
     * @return void
     */
    public function testErrorGetUser()
    {
        $uri = new Uri('/auth/facebook');
        $this->Request = $this->Request->withUri($uri);
        $this->Request = $this->Request->withQueryParams([
            'code' => 'ZPO9972j3092304230',
            'state' => '__TEST_STATE__'
        ]);
        $this->Request = $this->Request->withAttribute('params',[
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'socialLogin',
            'provider' => 'facebook'
        ]);
        $this->Request->getSession()->write('oauth2state','__TEST_STATE__');

        $Token = new \League\OAuth2\Client\Token\AccessToken([
            'access_token' => 'test-token',
            'expires' => 1490988496
        ]);

        $this->Provider->expects($this->never())
            ->method('getAuthorizationUrl');

        $this->Provider->expects($this->never())
            ->method('getState');

        $this->Provider->expects($this->any())
            ->method('getAccessToken')
            ->with(
                $this->equalTo('authorization_code'),
                $this->equalTo(['code' => 'ZPO9972j3092304230'])
            )
            ->will($this->returnValue($Token));

        $this->Provider->expects($this->any())
            ->method('getResourceOwner')
            ->will($this->throwException(new \Exception('Test error')));

        $Middleware = new SocialAuthMiddleware();

        $response = new Response();
        $next = function ($request, $response) {
            return compact('request', 'response');
        };

        $result = $Middleware($this->Request, $response, $next);
        $this->assertEquals(0, $result['request']->getAttribute('socialAuthStatus'));
        $this->assertEmpty($result['request']->getAttribute('socialRawData'));
        $this->assertEmpty($this->Request->getSession()->read('Auth'));
        $this->assertEquals(200, $result['response']->getStatusCode());
    }

    /**
     * Test when action is not valid for social login
     *
     * @return void
     */
    public function testNotValidAction()
    {
        $Middleware = new SocialAuthMiddleware();
        $response = new Response();
        $next = function ($request, $response) {
            return compact('request', 'response');
        };

        $result = $Middleware($this->Request, $response, $next);
        $this->assertTrue(is_array($result));

        $this->assertEquals(200, $result['response']->getStatusCode());
    }
}
