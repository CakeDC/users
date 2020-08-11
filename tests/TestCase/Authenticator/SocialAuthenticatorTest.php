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
namespace CakeDC\Users\Test\TestCase\Authenticator;

use Authentication\Authenticator\Result;
use Authentication\Identifier\IdentifierCollection;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequestFactory;
use Cake\TestSuite\TestCase;
use CakeDC\Auth\Social\MapUser;
use CakeDC\Auth\Social\Service\ServiceFactory;
use CakeDC\Users\Authenticator\SocialAuthenticator;
use CakeDC\Users\Exception\AccountNotActiveException;
use CakeDC\Users\Exception\MissingEmailException;
use CakeDC\Users\Exception\SocialAuthenticationException;
use CakeDC\Users\Exception\UserNotActiveException;
use CakeDC\Users\Model\Entity\User;
use League\OAuth2\Client\Provider\FacebookUser;
use Zend\Diactoros\Uri;

/**
 * Test Case for SocialAuthenticator class
 *
 * @package CakeDC\Users\Test\TestCase\Authenticator
 */
class SocialAuthenticatorTest extends TestCase
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
                'prefix' => null,
            ],
        ];
        Configure::write('OAuth.providers.facebook', $config);

        $this->Request = ServerRequestFactory::fromGlobals();
    }

    /**
     * Test authenticate method without social service
     *
     * @return void
     */
    public function testAuthenticateNoSocialService()
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

        $identifiers = new IdentifierCollection([
            'CakeDC/Users.Social',
        ]);
        $Authenticator = new SocialAuthenticator($identifiers);
        $Response = new Response();
        $result = $Authenticator->authenticate($this->Request, $Response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_CREDENTIALS_MISSING, $result->getStatus());
        $actual = $result->getData();
        $this->assertEmpty($actual);
    }

    /**
     * Test authenticate method with successfull authentication
     *
     * @return void
     */
    public function testAuthenticateSuccessfullyAuthenticated()
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

        $service = (new ServiceFactory())->createFromProvider('facebook');
        $this->Request = $this->Request->withAttribute('socialService', $service);
        $identifiers = new IdentifierCollection([
            'CakeDC/Users.Social',
        ]);
        $Authenticator = new SocialAuthenticator($identifiers);
        $Response = new Response();
        $result = $Authenticator->authenticate($this->Request, $Response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::SUCCESS, $result->getStatus());
        $actual = $result->getData();
        $this->assertInstanceOf(User::class, $actual);
        $this->assertEquals('test@gmail.com', $actual->email);
    }

    /**
     * Test authenticate method with error, getRawData is null
     *
     * @return void
     */
    public function testAuthenticateGetRawDataNull()
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

        $Token = new \League\OAuth2\Client\Token\AccessToken([
            'access_token' => 'test-token',
            'expires' => 1490988496,
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
            ->will($this->throwException(new \UnexpectedValueException('User not found')));

        $service = (new ServiceFactory())->createFromProvider('facebook');
        $this->Request = $this->Request->withAttribute('socialService', $service);
        $identifiers = new IdentifierCollection([
            'CakeDC/Users.Social',
        ]);
        $Authenticator = new SocialAuthenticator($identifiers);
        $Response = new Response();
        $result = $Authenticator->authenticate($this->Request, $Response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_IDENTITY_NOT_FOUND, $result->getStatus());
        $actual = $result->getData();
        $this->assertEmpty($actual);
    }

    /**
     * Test authenticate method when social users does not have email
     *
     * @return void
     */
    public function testAuthenticateErrorNoEmail()
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

        $Token = new \League\OAuth2\Client\Token\AccessToken([
            'access_token' => 'test-token',
            'expires' => 1490988496,
        ]);

        $user = new FacebookUser([
            'id' => '1',
            'name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
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

        $service = (new ServiceFactory())->createFromProvider('facebook');
        $this->Request = $this->Request->withAttribute('socialService', $service);
        $identifiers = new IdentifierCollection([
            'CakeDC/Users.Social',
        ]);
        $Authenticator = new SocialAuthenticator($identifiers);
        $Response = new Response();
        $result = false;
        try {
            $Authenticator->authenticate($this->Request, $Response);
        } catch (SocialAuthenticationException $e) {
            $rawData = ['token' => $Token] + $user->toArray();
            $mapper = new MapUser();
            $expected = [
                'rawData' => $mapper($service, $rawData),
            ];
            $actual = $e->getAttributes();
            $this->assertEquals($expected, $actual);
            $this->assertEquals(400, $e->getCode());

            $this->assertInstanceOf(MissingEmailException::class, $e->getPrevious());
            $result = true;
        }
        $this->assertTrue($result);
    }

    /**
     * Test authenticate method when social identifier return null
     *
     * @return void
     */
    public function testAuthenticateIdentifierReturnedNull()
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

        $Token = new \League\OAuth2\Client\Token\AccessToken([
            'access_token' => 'test-token',
            'expires' => 1490988496,
        ]);

        $user = new FacebookUser([
            'id' => '1',
            'name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
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

        $service = (new ServiceFactory())->createFromProvider('facebook');
        $this->Request = $this->Request->withAttribute('socialService', $service);
        $identifiers = new IdentifierCollection([]);
        $Authenticator = new SocialAuthenticator($identifiers);
        $Response = new Response();
        $Authenticator->authenticate($this->Request, $Response);
        $result = $Authenticator->authenticate($this->Request, $Response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(Result::FAILURE_IDENTITY_NOT_FOUND, $result->getStatus());
        $actual = $result->getData();
        $this->assertEmpty($actual);
    }

    /**
     * Data provider for testAuthenticateErrorException
     *
     * @return array
     */
    public function dataProviderAuthenticateErrorException()
    {
        return [
            [
                new AccountNotActiveException('Not Active'),
                SocialAuthenticator::FAILURE_ACCOUNT_NOT_ACTIVE,
            ],
            [
                new UserNotActiveException('Not Active'),
                SocialAuthenticator::FAILURE_USER_NOT_ACTIVE,
            ],
        ];
    }

    /**
     * Test authenticate method with successfull authentication
     *
     * @param \Exception $exception thrown exception
     * @param string $status expected status from Result object
     * @dataProvider dataProviderAuthenticateErrorException
     * @return void
     */
    public function testAuthenticateErrorException($exception, $status)
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

        $service = (new ServiceFactory())->createFromProvider('facebook');
        $this->Request = $this->Request->withAttribute('socialService', $service);
        $identifiers = $this->getMockBuilder(IdentifierCollection::class)->getMock();
        $identifiers->expects($this->once())
            ->method('identify')
            ->will($this->throwException($exception));

        $Authenticator = new SocialAuthenticator($identifiers);
        $Response = new Response();
        $result = $Authenticator->authenticate($this->Request, $Response);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($status, $result->getStatus());
        $actual = $result->getData();
        $this->assertEmpty($actual);
    }
}
