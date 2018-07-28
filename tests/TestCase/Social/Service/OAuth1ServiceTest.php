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


namespace CakeDC\Users\Test\TestCase\Social\Service;

use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\Http\ServerRequestFactory;
use Cake\Http\Session;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Social\Service\OAuth1Service;
use CakeDC\Users\Social\Service\ServiceInterface;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\User;
use Zend\Diactoros\Uri;

class OAuth1ServiceTest extends TestCase
{
    /**
     * @var \CakeDC\Users\Social\Service\OAuth1Service
     */
    public $Service;

    /**
     * @var \League\OAuth1\Client\Server\Server
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

        $this->Provider = $this->getMockBuilder('\League\OAuth1\Client\Server\Twitter')->setConstructorArgs([
            [
                'redirectUri' => '/auth/twitter',
                'linkSocialUri' => '/link-social/twitter',
                'callback_uri' => '/callback-link-social/twitter',
                'identifier' => '20003030300303',
                'secret' => 'weakpassword','identifier' => 'clientId',
            ],
        ])->setMethods([
            'getTemporaryCredentials', 'getAuthorizationUrl', 'getTokenCredentials', 'getUserDetails'
        ])->getMock();

        $config = [
            'service' => 'CakeDC\Users\Social\Service\OAuth1Service',
            'className' => $this->Provider,
            'mapper' => 'CakeDC\Users\Social\Mapper\Twitter',
            'options' => [],
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

        $this->Service = new OAuth1Service($config);

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

        unset($this->Provider, $this->Service, $this->Request);
    }

    /**
     * Test construct
     *
     * @return void
     */
    public function testConstruct()
    {

        $service = new OAuth1Service([
            'className' => 'League\OAuth1\Client\Server\Twitter',
            'mapper' => 'CakeDC\Users\Social\Mapper\Twitter',
            'options' => [
                'redirectUri' => '/auth/twitter',
                'linkSocialUri' => '/link-social/twitter',
                'callbackLinkSocialUri' => '/callback-link-social/twitter',
                'clientId' => '20003030300303',
                'clientSecret' => 'weakpassword'
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
        ]);
        $this->assertInstanceOf(ServiceInterface::class, $service);
    }

    /**
     * Test getAuthorizationUrl
     *
     * @return void
     */
    public function testGetAuthorizationUrl()
    {
        $Credentials = new TemporaryCredentials();
        $Credentials->setIdentifier('404405646989097789546879');
        $Credentials->setSecret('secretpasword');

        $this->Provider->expects($this->at(0))
            ->method('getTemporaryCredentials')
            ->will($this->returnValue($Credentials));

        $this->Provider->expects($this->at(1))
            ->method('getAuthorizationUrl')
            ->with(
                $this->equalTo($Credentials)
            )
            ->will($this->returnValue('http://twitter.com/redirect/url'));

        $actual = $this->Service->getAuthorizationUrl($this->Request);
        $expected = 'http://twitter.com/redirect/url';
        $this->assertEquals($expected, $actual);

        $expected = $Credentials;
        $actual = $this->Request->getSession()->read('temporary_credentials');
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test isGetUserStep, should return true
     *
     * @return void
     */
    public function testIsGetUserStep()
    {
        $uri = new Uri('/login');

        $sessionConfig = (array)Configure::read('Session') + [
            'defaults' => 'php',
        ];
        $session = Session::create($sessionConfig);
        $this->Request = new ServerRequest([
            'uri' => $uri,
            'session' => $session,
        ]);
        $this->Request = $this->Request->withQueryParams([
            'oauth_token' => 'dfio39972j3092304230',
            'oauth_verifier' => '21312h2312390839012',
        ]);

        $result = $this->Service->isGetUserStep($this->Request);
        $this->assertTrue($result);
    }

    /**
     * Test isGetUserStep, when values are empty
     *
     * @return void
     */
    public function testIsGetUserStepWhenAllEmpty()
    {
        $uri = new Uri('/login');

        $sessionConfig = (array)Configure::read('Session') + [
                'defaults' => 'php',
            ];
        $session = Session::create($sessionConfig);
        $this->Request = new ServerRequest([
            'uri' => $uri,
            'session' => $session,
        ]);
        $this->Request = $this->Request->withQueryParams([
            'oauth_token' => '',
            'oauth_verifier' => '',
        ]);

        $result = $this->Service->isGetUserStep($this->Request);
        $this->assertFalse($result);

    }

    /**
     * Test isGetUserStep, when oauth_token value is empty
     *
     * @return void
     */
    public function testIsGetUserStepWhenOauthTokenEmpty()
    {

        $uri = new Uri('/login');

        $sessionConfig = (array)Configure::read('Session') + [
                'defaults' => 'php',
            ];
        $session = Session::create($sessionConfig);
        $this->Request = new ServerRequest([
            'uri' => $uri,
            'session' => $session,
        ]);
        $this->Request = $this->Request->withQueryParams([
            'oauth_token' => '',
            'oauth_verifier' => '21312h2312390839012',
        ]);

        $result = $this->Service->isGetUserStep($this->Request);
        $this->assertFalse($result);
    }

    /**
     * Test isGetUserStep, when oauth_verifier value is empty
     *
     * @return void
     */
    public function testIsGetUserStepWhenOauthVerifierEmpty()
    {

        $uri = new Uri('/login');

        $sessionConfig = (array)Configure::read('Session') + [
                'defaults' => 'php',
            ];
        $session = Session::create($sessionConfig);
        $this->Request = new ServerRequest([
            'uri' => $uri,
            'session' => $session,
        ]);
        $this->Request = $this->Request->withQueryParams([
            'oauth_token' => 'dfio39972j3092304230',
            'oauth_verifier' => '',
        ]);

        $result = $this->Service->isGetUserStep($this->Request);
        $this->assertFalse($result);
    }

    /**
     * Test isGetUserStep, when keys not present
     *
     * @return void
     */
    public function testIsGetUserStepWhenOauthKeysNotPresent()
    {

        $uri = new Uri('/login');

        $sessionConfig = (array)Configure::read('Session') + [
                'defaults' => 'php',
            ];
        $session = Session::create($sessionConfig);
        $this->Request = new ServerRequest([
            'uri' => $uri,
            'session' => $session,
        ]);


        $result = $this->Service->isGetUserStep($this->Request);
        $this->assertFalse($result);
    }

    /**
     * Test getUser method
     *
     * @return void
     */
    public function testGetUser()
    {
        $this->Request = $this->Request->withQueryParams([
            'oauth_token' => 'good39972j3092304230',
            'oauth_verifier' => '77312h2312390839012',
        ]);

        $Credentials = new TemporaryCredentials();
        $Credentials->setIdentifier('404405646989097789546879');
        $Credentials->setSecret('secretpasword');
        $this->Request->getSession()->write('temporary_credentials', $Credentials);

        $TokenCredentials = new TokenCredentials();
        $TokenCredentials->setSecret('tokensecretpasswordnew');
        $TokenCredentials->setIdentifier('50589595670964649809890');

        $user = new User();

        $user->uid = '5698297389-2332-89879';
        $user->nickname = 'rmarcelo';
        $user->name = 'Marcelo';
        $user->location = 'Brazil';
        $user->description = 'Developer';
        $user->imageUrl = null;
        $user->email = 'example@example.com';

        $this->Provider->expects($this->never())
            ->method('getTemporaryCredentials');

        $this->Provider->expects($this->at(0))
            ->method('getTokenCredentials')
            ->with(
                $this->equalTo($Credentials),
                $this->equalTo('good39972j3092304230'),
                $this->equalTo('77312h2312390839012')
            )
            ->will($this->returnValue($TokenCredentials));

        $this->Provider->expects($this->at(1))
            ->method('getUserDetails')
            ->with(
                $this->equalTo($TokenCredentials)
            )
            ->will($this->returnValue($user));

        $actual = $this->Service->getUser($this->Request);

        $expected = [
            'uid' => '5698297389-2332-89879',
            'nickname' => 'rmarcelo',
            'name' => 'Marcelo',
            'firstName' => null,
            'lastName' => null,
            'email' => 'example@example.com',
            'location' => 'Brazil',
            'description' => 'Developer',
            'imageUrl' => null,
            'urls' => [],
            'extra' => [],
            'token' => [
                'accessToken' => '50589595670964649809890',
                'tokenSecret' => 'tokensecretpasswordnew'
            ]
        ];
        $this->assertEquals($expected, $actual);
    }
}
