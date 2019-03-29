<?php
/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Controller\Traits;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Http\ServerRequestFactory;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use League\OAuth2\Client\Provider\FacebookUser;
use Zend\Diactoros\Uri;

class LinkSocialTraitTest extends BaseTraitTest
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.SocialAccounts',
        'plugin.CakeDC/Users.Users'
    ];

    /**
     * @var \League\OAuth2\Client\Provider\Facebook
     */
    public $Provider;

    /**
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->traitClassName = 'CakeDC\Users\Controller\Traits\LinkSocialTrait';
        $this->traitMockMethods = ['dispatchEvent', 'isStopped', 'redirect', 'getUsersTable', 'set'];

        parent::setUp();
        $request = new ServerRequest();
        $this->Trait = $this->getMockBuilder('CakeDC\Users\Controller\Traits\LinkSocialTrait')
            ->setMethods(['dispatchEvent', 'redirect', 'set'])
            ->getMockForTrait();

        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['setConfig'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->Trait->request = $request;

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
    }

    /**
     * test linkSocial method
     *
     * @return void
     */
    public function testLinkSocialHappy()
    {
        Configure::write('OAuth.providers.facebook.options.clientId', 'testclientidtestclientid');
        Configure::write('OAuth.providers.facebook.options.clientSecret', 'testclientsecrettestclientsecret');

        $this->Trait = $this->getMockBuilder('CakeDC\Users\Controller\Traits\LinkSocialTrait')
            ->setMethods(['dispatchEvent', 'redirect', 'set', '_createSocialProvider', 'getUsersTable', 'log'])
            ->getMockForTrait();

        $this->Trait->request = ServerRequestFactory::fromGlobals();
        $this->Trait->request->getSession()->write('oauth2state', '__TEST_STATE__');
        $uri = new Uri('/callback-link-social/facebook');

        $this->Trait->request = $this->Trait->request->withUri($uri);
        $this->Trait->request = $this->Trait->request->withQueryParams([
            'code' => 'ZPO9972j3092304230',
            'state' => '__TEST_STATE__'
        ]);
        $this->Trait->request = $this->Trait->request->withAttribute('params', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'linkSocial',
            'provider' => 'facebook'
        ]);

        $this->_mockAuthLoggedIn();
        $this->_mockDispatchEvent(new Event('event'));
        $this->_mockFlash();

        $this->Provider->expects($this->any())
            ->method('getState')
            ->will($this->returnValue('_NEW_STATE_'));

        $this->Provider->expects($this->any())
            ->method('getAuthorizationUrl')
            ->will($this->returnValue('http://facebook.com/redirect/url'));

        $this->Trait->Flash->expects($this->never())
            ->method('error');

        $this->Trait->Flash->expects($this->never())
            ->method('success');

        $ProviderMock = $this->getMockBuilder('League\OAuth2\Client\Provider\Facebook')
            ->setMethods(['getAuthorizationUrl', 'getState'])
            ->disableOriginalConstructor()
            ->getMock();

        $ProviderMock->expects($this->once())
            ->method('getAuthorizationUrl')
            ->will($this->returnValue('http://localhost/fake/facebook/login'));

        $ProviderMock->expects($this->once())
            ->method('getState')
            ->will($this->returnValue('a3423ja9ads90u3242309'));

        $this->Trait->expects($this->once())
            ->method('_createSocialProvider')
            ->with(
                $this->equalTo([
                    'className' => 'League\OAuth2\Client\Provider\Facebook',
                    'options' => [
                        'graphApiVersion' => 'v2.8',
                        'redirectUri' => '/auth/facebook',
                        'linkSocialUri' => '/link-social/facebook',
                        'callbackLinkSocialUri' => '/callback-link-social/facebook',
                        'clientId' => 'testclientidtestclientid',
                        'clientSecret' => 'testclientsecrettestclientsecret'
                    ],
                    'authParams' => ['scope' => ['public_profile', 'email', 'user_birthday', 'user_gender', 'user_link']],
                ])
            )
            ->will($this->returnValue($ProviderMock));

        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with($this->equalTo('http://facebook.com/redirect/url'))
            ->will($this->returnValue(new Response()));

        $this->Trait->linkSocial('facebook');
    }

    /**
     * test
     *
     * @return void
     */
    public function testCallbackLinkSocialHappy()
    {
        Configure::write('OAuth.providers.facebook.options.clientId', 'testclientidtestclientid');
        Configure::write('OAuth.providers.facebook.options.clientSecret', 'testclientsecrettestclientsecret');

        $Table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');

        $Token = new \League\OAuth2\Client\Token\AccessToken([
            'access_token' => 'test-token',
            'expires' => 1490988496
        ]);

        $user = new FacebookUser([
            'id' => '9999911112255',
            'name' => 'Ful Name.',
            'username' => 'mock_username',
            'first_name' => 'First Name',
            'last_name' => 'Last name',
            'email' => 'user-1@test.com',
            'Location' => 'mock_home',
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
            'link' => 'facebook-link-15579',
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

        $this->Trait = $this->getMockBuilder('CakeDC\Users\Controller\Traits\LinkSocialTrait')
            ->setMethods(['dispatchEvent', 'redirect', 'set', 'getUsersTable', 'log'])
            ->getMockForTrait();

        $this->Trait->request = ServerRequestFactory::fromGlobals();
        $this->Trait->request->getSession()->write('oauth2state', '__TEST_STATE__');
        $uri = new Uri('/callback-link-social/facebook');

        $this->Trait->request = $this->Trait->request->withUri($uri);
        $this->Trait->request = $this->Trait->request->withQueryParams([
            'code' => 'ZPO9972j3092304230',
            'state' => '__TEST_STATE__'
        ]);
        $this->Trait->request = $this->Trait->request->withAttribute('params', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'linkSocial',
            'provider' => 'facebook'
        ]);

        $this->Trait->expects($this->any())
            ->method('getUsersTable')
            ->will($this->returnValue($Table));

        $this->_mockAuthLoggedIn();
        $this->_mockDispatchEvent(new Event('event'));
        $this->_mockFlash();
        $this->Trait->Flash->expects($this->never())
            ->method('error');

        $this->Trait->Flash->expects($this->once())
            ->method('success')
            ->with(__d('CakeDC/Users', 'Social account was associated.'));

        $fbToken = new AccessToken([
            'access_token' => 'token',
            'tokenSecret' => null,
            'expires' => 1458423682
        ]);
        $ProviderMock = $this->getMockBuilder('League\OAuth2\Client\Provider\Facebook')
            ->setMethods(['getAccessToken', 'getResourceOwner'])
            ->disableOriginalConstructor()
            ->getMock();

        $ProviderMock->expects($this->once())
            ->method('getAccessToken')
            ->with(
                $this->equalTo('authorization_code'),
                $this->equalTo([
                    'code' => '99999000222220'
                ])
            )->will($this->returnValue($fbToken));

        $fbUser = new FacebookUser([
            'id' => '9999911112255',
            'name' => 'Ful Name.',
            'username' => 'mock_username',
            'first_name' => 'First Name',
            'last_name' => 'Last name',
            'email' => 'user-1@test.com',
            'Location' => 'mock_home',
            'bio' => 'mock_description',
            'link' => 'facebook-link-15579',
        ]);
        $ProviderMock->expects($this->once())
            ->method('getResourceOwner')
            ->with(
                $this->equalTo($fbToken)
            )->will($this->returnValue($fbUser));

        $this->Trait->expects($this->once())
            ->method('_createSocialProvider')
            ->with(
                $this->equalTo([
                    'className' => 'League\OAuth2\Client\Provider\Facebook',
                    'options' => [
                        'graphApiVersion' => 'v2.8',
                        'redirectUri' => '/auth/facebook',
                        'linkSocialUri' => '/link-social/facebook',
                        'callbackLinkSocialUri' => '/callback-link-social/facebook',
                        'clientId' => 'testclientidtestclientid',
                        'clientSecret' => 'testclientsecrettestclientsecret'
                    ],
                    'authParams' => ['scope' => ['public_profile', 'email', 'user_birthday', 'user_gender', 'user_link']],
                ])
            )
            ->will($this->returnValue($ProviderMock));

        $this->Trait->callbackLinkSocial('facebook');

        $actual = $Table->SocialAccounts->find('all')->where(['reference' => '9999911112255'])->firstOrFail();

        $expiresTime = new Time();
        $tokenExpires = $expiresTime->setTimestamp($Token->getExpires())->format('Y-m-d H:i:s');

        $expected = [
            'provider' => 'facebook',
            'username' => 'mock_username',
            'reference' => '9999911112255',
            'avatar' => 'https://graph.facebook.com/9999911112255/picture?type=large',
            'description' => 'I am the best test user in the world.',
            'token' => 'test-token',
            'token_secret' => null,
            'user_id' => '00000000-0000-0000-0000-000000000001',
            'active' => true
        ];
        foreach ($expected as $property => $value) {
            $this->assertEquals($value, $actual->$property);
        }
        $this->assertEquals($tokenExpires, $actual->token_expires->format('Y-m-d H:i:s'));
    }

    /**
     * test
     *
     * @return void
     */
    public function testCallbackLinkSocialWithValidationErrors()
    {
        Configure::write('OAuth.providers.facebook.options.clientId', 'testclientidtestclientid');
        Configure::write('OAuth.providers.facebook.options.clientSecret', 'testclientsecrettestclientsecret');
        $user = TableRegistry::getTableLocator()->get('CakeDC/Users.Users')->get('00000000-0000-0000-0000-000000000001');
        $user->setErrors([
            'social_accounts' => [
                '_existsIn' => __d('cake_d_c/users', 'Social account already associated to another user')
            ]
        ]);
        $Table = $this->getMockForModel('CakeDC/Users.Users', ['linkSocialAccount', 'get']);
        $Table->setAlias('Users');

        $Table->expects($this->once())
            ->method('get')
            ->will($this->returnValue($user));

        $Table->expects($this->once())
            ->method('linkSocialAccount')
            ->will($this->returnValue($user));

        $Token = new \League\OAuth2\Client\Token\AccessToken([
            'access_token' => 'test-token',
            'expires' => 1490988496
        ]);

        $user = new FacebookUser([
            'id' => '9999911112255',
            'name' => 'Ful Name.',
            'username' => 'mock_username',
            'first_name' => 'First Name',
            'last_name' => 'Last name',
            'email' => 'user-1@test.com',
            'Location' => 'mock_home',
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
            'link' => 'facebook-link-15579',
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
                $this->equalTo([
                    'className' => 'League\OAuth2\Client\Provider\Facebook',
                    'options' => [
                        'graphApiVersion' => 'v2.8',
                        'redirectUri' => '/auth/facebook',
                        'linkSocialUri' => '/link-social/facebook',
                        'callbackLinkSocialUri' => '/callback-link-social/facebook',
                        'clientId' => 'testclientidtestclientid',
                        'clientSecret' => 'testclientsecrettestclientsecret'
                    ],
                    'authParams' => ['scope' => ['public_profile', 'email', 'user_birthday', 'user_gender', 'user_link']],
                ])
            )
            ->will($this->returnValue($user));

        $this->Trait = $this->getMockBuilder('CakeDC\Users\Controller\Traits\LinkSocialTrait')
            ->setMethods(['dispatchEvent', 'redirect', 'set', 'getUsersTable', 'log'])
            ->getMockForTrait();

        $this->Trait->expects($this->any())
            ->method('getUsersTable')
            ->will($this->returnValue($Table));

        $this->Trait->request = ServerRequestFactory::fromGlobals();
        $this->Trait->request->getSession()->write('oauth2state', '__TEST_STATE__');
        $uri = new Uri('/callback-link-social/facebook');

        $this->Trait->request = $this->Trait->request->withUri($uri);
        $this->Trait->request = $this->Trait->request->withQueryParams([
            'code' => 'ZPO9972j3092304230',
            'state' => '__TEST_STATE__'
        ]);
        $this->Trait->request = $this->Trait->request->withAttribute('params', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'linkSocial',
            'provider' => 'facebook'
        ]);

        $this->_mockAuthLoggedIn();
        $this->_mockDispatchEvent(new Event('event'));
        $this->_mockFlash();
        $this->Trait->Flash->expects($this->once())
            ->method('error');

        $this->Trait->Flash->expects($this->never())
            ->method('success');

        $ProviderMock = $this->getMockBuilder('League\OAuth2\Client\Provider\Facebook')
            ->setMethods(['getAccessToken', 'getResourceOwner'])
            ->disableOriginalConstructor()
            ->getMock();

        $ProviderMock->expects($this->once())
            ->method('getAccessToken')
            ->with(
                $this->equalTo('authorization_code'),
                $this->equalTo([
                    'code' => '99999000222220'
                ])
            )->will($this->throwException(new \Exception));

        $ProviderMock->expects($this->never())
            ->method('getResourceOwner');

        $this->Trait->expects($this->once())
            ->method('_createSocialProvider')
            ->with(
                $this->equalTo([
                    'className' => 'League\OAuth2\Client\Provider\Facebook',
                    'options' => [
                        'graphApiVersion' => 'v2.8',
                        'redirectUri' => '/auth/facebook',
                        'linkSocialUri' => '/link-social/facebook',
                        'callbackLinkSocialUri' => '/callback-link-social/facebook',
                        'clientId' => 'testclientidtestclientid',
                        'clientSecret' => 'testclientsecrettestclientsecret'
                    ],
                    'authParams' => ['scope' => ['public_profile', 'email', 'user_birthday', 'user_gender', 'user_link']],
                ])
            )
            ->will($this->returnValue($ProviderMock));

        $this->Trait->callbackLinkSocial('facebook');

        $actual = $Table->SocialAccounts->exists(['reference' => '9999911112255']);
        $this->assertFalse($actual);
    }

    /**
     * test
     *
     * @return void
     */
    public function testCallbackLinkSocialQueryHasErrors()
    {
        Configure::write('OAuth.providers.facebook.options.clientId', 'testclientidtestclientid');
        Configure::write('OAuth.providers.facebook.options.clientSecret', 'testclientsecrettestclientsecret');

        $Table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');

        $this->Provider->expects($this->never())
            ->method('getAuthorizationUrl');

        $this->Provider->expects($this->never())
            ->method('getState');

        $this->Provider->expects($this->never())
            ->method('getAccessToken');

        $this->Provider->expects($this->never())
            ->method('getResourceOwner');

        $this->Trait = $this->getMockBuilder('CakeDC\Users\Controller\Traits\LinkSocialTrait')
            ->setMethods(['dispatchEvent', 'redirect', 'set', 'getUsersTable', 'log'])
            ->getMockForTrait();

        $this->Trait->request = ServerRequestFactory::fromGlobals();
        $this->Trait->request->getSession()->write('oauth2state', '__TEST_STATE__');
        $uri = new Uri('/callback-link-social/facebook');

        $this->Trait->request = $this->Trait->request->withUri($uri);
        $this->Trait->request = $this->Trait->request->withAttribute('params', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'linkSocial',
            'provider' => 'facebook'
        ]);

        $this->Trait->expects($this->any())
            ->method('getUsersTable')
            ->will($this->returnValue($Table));
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with($this->equalTo([
                'action' => 'profile'
            ]))
            ->will($this->returnValue(new Response()));
        $this->_mockAuthLoggedIn();
        $this->_mockDispatchEvent(new Event('event'));
        $this->_mockFlash();
        $this->Trait->Flash->expects($this->never())
            ->method('success');

        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with(__d('cake_d_c/users', 'Could not associate account, please try again.'));

        $this->Trait->expects($this->once())
            ->method('_createSocialProvider')
            ->with(
                $this->equalTo([
                    'className' => 'League\OAuth2\Client\Provider\Facebook',
                    'options' => [
                        'graphApiVersion' => 'v2.8',
                        'redirectUri' => '/auth/facebook',
                        'linkSocialUri' => '/link-social/facebook',
                        'callbackLinkSocialUri' => '/callback-link-social/facebook',
                        'clientId' => 'testclientidtestclientid',
                        'clientSecret' => 'testclientsecrettestclientsecret'
                    ],
                    'authParams' => ['scope' => ['public_profile', 'email', 'user_birthday', 'user_gender', 'user_link']],
                ])
            )
            ->will($this->returnValue($ProviderMock));

        $result = $this->Trait->callbackLinkSocial('facebook');
        $this->assertInstanceOf(Response::class, $result);
    }

    /**
     * test
     *
     * @return void
     */
    public function testCallbackLinkSocialUnknownProvider()
    {
        Configure::write('OAuth.providers.facebook.options.clientId', 'testclientidtestclientid');
        Configure::write('OAuth.providers.facebook.options.clientSecret', 'testclientsecrettestclientsecret');

        $this->Provider->expects($this->never())
            ->method('getAuthorizationUrl');

        $this->Provider->expects($this->never())
            ->method('getState');

        $this->Provider->expects($this->never())
            ->method('getAccessToken');

        $this->Provider->expects($this->never())
            ->method('getResourceOwner');

        $this->Trait->expects($this->once())
            ->method('_createSocialProvider')
            ->with(
                $this->equalTo([
                    'className' => 'League\OAuth2\Client\Provider\Facebook',
                    'options' => [
                        'graphApiVersion' => 'v2.8',
                        'redirectUri' => '/auth/facebook',
                        'linkSocialUri' => '/link-social/facebook',
                        'callbackLinkSocialUri' => '/callback-link-social/facebook',
                        'clientId' => 'testclientidtestclientid',
                        'clientSecret' => 'testclientsecrettestclientsecret'
                    ],
                    'authParams' => ['scope' => ['public_profile', 'email', 'user_birthday', 'user_gender', 'user_link']],
                ])
            )
            ->will($this->returnValue($ProviderMock));

        $this->Trait->callbackLinkSocial('facebook');
    }

    /**
     * test
     *
     * @return void
     */
    public function testCallbackLinkSocialMissingCode()
    {
        Configure::write('OAuth.providers.facebook.options.clientId', 'testclientidtestclientid');
        Configure::write('OAuth.providers.facebook.options.clientSecret', 'testclientsecrettestclientsecret');

        $Table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');

        $this->Trait = $this->getMockBuilder('CakeDC\Users\Controller\Traits\LinkSocialTrait')
            ->setMethods(['dispatchEvent', 'redirect', 'set', 'getUsersTable', 'log'])
            ->getMockForTrait();

        $this->Trait->request = ServerRequestFactory::fromGlobals();
        $this->Trait->request->getSession()->write('oauth2state', '__TEST_STATE__');
        $uri = new Uri('/callback-link-social/facebook');

        $this->Trait->request = $this->Trait->request->withUri($uri);
        $this->Trait->request = $this->Trait->request->withAttribute('params', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'linkSocial',
            'provider' => 'unknown'
        ]);

        $this->Trait->expects($this->never())
            ->method('getUsersTable');

        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with($this->equalTo([
                'action' => 'profile'
            ]))
            ->will($this->returnValue(new Response()));
        $this->_mockAuthLoggedIn();
        $this->_mockDispatchEvent(new Event('event'));
        $this->_mockFlash();
        $this->Trait->Flash->expects($this->never())
            ->method('success');

        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with(__d('cake_d_c/users', 'Could not associate account, please try again.'));

        $result = $this->Trait->callbackLinkSocial('unknown');
        $this->assertInstanceOf(Response::class, $result);
    }
}
