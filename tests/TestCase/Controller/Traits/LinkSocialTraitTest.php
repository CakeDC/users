<?php
/**
 * Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Controller\Traits;

use CakeDC\Users\Controller\Traits\LinkSocialTrait;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Cake\I18n\Time;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use League\OAuth2\Client\Provider\Facebook;
use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Token\AccessToken;

class LinkSocialTraitTest extends BaseTraitTest
{
    /**
     * Keep the original config for oauth
     *
     * @var array
     */
    private $oauthConfig;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.social_accounts',
        'plugin.CakeDC/Users.users'
    ];

    /**
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        if ($this->oauthConfig === null) {
            $this->oauthConfig = Configure::read('OAuth');
        }
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
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        Configure::write('OAuth', $this->oauthConfig);
        parent::tearDown();
    }

    /**
     * mock request for GET
     *
     * @return void
     */
    protected function _mockRequestGet($withSession = false)
    {
        $methods = ['is', 'referer', 'getData', 'getQuery', 'getQueryParams'];

        if ($withSession) {
            $methods[] = 'session';
        }

        $this->Trait->request = $this->getMockBuilder('Cake\Network\Request')
                ->setMethods($methods)
                ->getMock();
        $this->Trait->request->expects($this->any())
                ->method('is')
                ->with('post')
                ->will($this->returnValue(false));
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

        $this->_mockRequestGet(true);
        $this->_mockAuthLoggedIn();
        $this->_mockDispatchEvent(new Event('event'));
        $this->_mockFlash();
        $this->_mockSession([]);
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
                        'graphApiVersion' => 'v2.5',
                        'redirectUri' => '/auth/facebook',
                        'linkSocialUri' => '/link-social/facebook',
                        'callbackLinkSocialUri' => '/callback-link-social/facebook',
                        'clientId' => 'testclientidtestclientid',
                        'clientSecret' => 'testclientsecrettestclientsecret'
                    ]
                ])
            )
            ->will($this->returnValue($ProviderMock));

        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(
                $this->equalTo('http://localhost/fake/facebook/login')
            );

        $this->Trait->linkSocial('facebook');
    }

    /**
     * test
     *
     * @return void
     */
    public function testLinkSocialNotDefineLinkSocialRedirectUri()
    {
        Configure::write('OAuth.providers.facebook.options.clientId', 'testclientidtestclientid');
        Configure::write('OAuth.providers.facebook.options.clientSecret', 'testclientsecrettestclientsecret');
        Configure::delete('OAuth.providers.facebook.options.callbackLinkSocialUri');

        $result = false;
        try {
            $this->_mockRequestGet();
            $this->_mockAuthLoggedIn();
            $this->_mockFlash();

            $this->_mockDispatchEvent(new Event('event'));

            $this->Trait->linkSocial('facebook');
        } catch (NotFoundException $e) {
            $result = true;
        }
        $this->assertTrue($result);
    }

    /**
     * test
     *
     * @return void
     */
    public function testLinkSocialNotDefinedClientId()
    {
        Configure::delete('OAuth.providers.facebook.options.clientId');
        Configure::write('OAuth.providers.facebook.options.clientSecret', 'testclientsecrettestclientsecret');
        $result = false;
        try {
            $this->_mockRequestGet();
            $this->_mockAuthLoggedIn();
            $this->_mockFlash();

            $this->_mockDispatchEvent(new Event('event'));

            $this->Trait->linkSocial('facebook');
        } catch (NotFoundException $e) {
            $result = true;
        }
        $this->assertTrue($result);
    }

    /**
     * test
     *
     * @return void
     */
    public function testLinkSocialNotDefinedClientSecret()
    {
        Configure::write('OAuth.providers.facebook.options.clientId', 'testclientidtestclientid');
        Configure::delete('OAuth.providers.facebook.options.clientSecret', 'testclientsecrettestclientsecret');
        $result = false;
        try {
            $this->_mockRequestGet();
            $this->_mockAuthLoggedIn();
            $this->_mockFlash();

            $this->_mockDispatchEvent(new Event('event'));

            $this->Trait->linkSocial('facebook');
        } catch (NotFoundException $e) {
            $result = true;
        }
        $this->assertTrue($result);
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

        $Table = TableRegistry::get('CakeDC/Users.Users');

        $this->Trait = $this->getMockBuilder('CakeDC\Users\Controller\Traits\LinkSocialTrait')
            ->setMethods(['dispatchEvent', 'redirect', 'set', '_createSocialProvider', 'getUsersTable', 'log'])
            ->getMockForTrait();

        $this->Trait->expects($this->any())
            ->method('getUsersTable')
            ->will($this->returnValue($Table));

        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['setConfig'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockRequestGet(true);
        $this->Trait->request->expects($this->once())
                ->method('getQuery')
                ->with('code')
                ->will($this->returnValue('99999000222220'));

        $this->Trait->request->expects($this->once())
                ->method('getQueryParams')
                ->will($this->returnValue([
                    'code' => '99999000222220',
                    'state' => 'a393j2942789'
                ]));

        $this->_mockSession([
            'SocialLink' => [
                'oauth2state' => 'a393j2942789'
            ]
        ]);
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
                        'graphApiVersion' => 'v2.5',
                        'redirectUri' => '/auth/facebook',
                        'linkSocialUri' => '/link-social/facebook',
                        'callbackLinkSocialUri' => '/callback-link-social/facebook',
                        'clientId' => 'testclientidtestclientid',
                        'clientSecret' => 'testclientsecrettestclientsecret'
                    ]
                ])
            )
            ->will($this->returnValue($ProviderMock));

        $this->Trait->callbackLinkSocial('facebook');

        $actual = $Table->SocialAccounts->find('all')->where(['reference' => '9999911112255'])->firstOrFail();

        $expiresTime = new Time();
        $tokenExpires = $expiresTime->setTimestamp(1458423682)->format('Y-m-d H:i:s');

        $expected = [
            'provider' => 'Facebook',
            'username' => 'mock_username',
            'reference' => '9999911112255',
            'avatar' => 'https://graph.facebook.com/9999911112255/picture?type=large',
            'description' => 'mock_description',
            'token' => 'token',
            'token_secret' => null,
            'user_id' => '00000000-0000-0000-0000-000000000001',
            'active' => true
        ];
        foreach ($expected as $property => $value) {
            $check = $actual->$property;
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
        $user = TableRegistry::get('akeDC/Users.Users')->get('00000000-0000-0000-0000-000000000001');
        $user->errors([
            'social_accounts' => [
                '_existsIn' => __d('CakeDC/Users', 'Social account already associated to another user')
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

        $this->Trait = $this->getMockBuilder('CakeDC\Users\Controller\Traits\LinkSocialTrait')
            ->setMethods(['dispatchEvent', 'redirect', 'set', '_createSocialProvider', 'getUsersTable', 'log'])
            ->getMockForTrait();

        $this->Trait->expects($this->any())
            ->method('getUsersTable')
            ->will($this->returnValue($Table));

        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['setConfig'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockRequestGet(true);
        $this->Trait->request->expects($this->once())
                ->method('getQuery')
                ->with('code')
                ->will($this->returnValue('99999000222220'));

        $this->Trait->request->expects($this->once())
                ->method('getQueryParams')
                ->will($this->returnValue([
                    'code' => '99999000222220',
                    'state' => 'a393j2942789'
                ]));

        $this->_mockSession([
            'SocialLink' => [
                'oauth2state' => 'a393j2942789'
            ]
        ]);
        $this->_mockAuthLoggedIn();
        $this->_mockDispatchEvent(new Event('event'));
        $this->_mockFlash();
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with(__d('CakeDC/Users', 'Could not associate account, please try again.'));

        $this->Trait->Flash->expects($this->never())
            ->method('success');

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
                        'graphApiVersion' => 'v2.5',
                        'redirectUri' => '/auth/facebook',
                        'linkSocialUri' => '/link-social/facebook',
                        'callbackLinkSocialUri' => '/callback-link-social/facebook',
                        'clientId' => 'testclientidtestclientid',
                        'clientSecret' => 'testclientsecrettestclientsecret'
                    ]
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
    public function testCallbackLinkSocialFailGettingAccessToken()
    {
        Configure::write('OAuth.providers.facebook.options.clientId', 'testclientidtestclientid');
        Configure::write('OAuth.providers.facebook.options.clientSecret', 'testclientsecrettestclientsecret');

        $Table = TableRegistry::get('CakeDC/Users.Users');

        $this->Trait = $this->getMockBuilder('CakeDC\Users\Controller\Traits\LinkSocialTrait')
            ->setMethods(['dispatchEvent', 'redirect', 'set', '_createSocialProvider', 'getUsersTable', 'log'])
            ->getMockForTrait();

        $this->Trait->expects($this->any())
            ->method('getUsersTable')
            ->will($this->returnValue($Table));

        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['setConfig'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockRequestGet(true);
        $this->Trait->request->expects($this->once())
                ->method('getQuery')
                ->with('code')
                ->will($this->returnValue('99999000222220'));

        $this->Trait->request->expects($this->once())
                ->method('getQueryParams')
                ->will($this->returnValue([
                    'code' => '99999000222220',
                    'state' => 'a393j2942789'
                ]));

        $this->_mockSession([
            'SocialLink' => [
                'oauth2state' => 'a393j2942789'
            ]
        ]);
        $this->_mockAuthLoggedIn();
        $this->_mockDispatchEvent(new Event('event'));
        $this->_mockFlash();
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with(__d('CakeDC/Users', 'Could not associate account, please try again.'));

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
                        'graphApiVersion' => 'v2.5',
                        'redirectUri' => '/auth/facebook',
                        'linkSocialUri' => '/link-social/facebook',
                        'callbackLinkSocialUri' => '/callback-link-social/facebook',
                        'clientId' => 'testclientidtestclientid',
                        'clientSecret' => 'testclientsecrettestclientsecret'
                    ]
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

        $Table = TableRegistry::get('CakeDC/Users.Users');

        $this->Trait = $this->getMockBuilder('CakeDC\Users\Controller\Traits\LinkSocialTrait')
            ->setMethods(['dispatchEvent', 'redirect', 'set', '_createSocialProvider', 'getUsersTable', 'log'])
            ->getMockForTrait();

        $this->Trait->expects($this->any())
            ->method('getUsersTable')
            ->will($this->returnValue($Table));

        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['setConfig'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockRequestGet(true);
        $this->Trait->request->expects($this->never())
                ->method('getQuery');

        $this->Trait->request->expects($this->once())
                ->method('getQueryParams')
                ->will($this->returnValue([
                    'error' => 'We got some error',
                    'code' => '99999000222220',
                    'state' => 'a393j2942789'
                ]));
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(
                $this->equalTo(['action' => 'profile'])
            );

        $this->_mockSession([
            'SocialLink' => [
                'oauth2state' => 'a393j2942789'
            ]
        ]);
        $this->_mockAuthLoggedIn();
        $this->_mockDispatchEvent(new Event('event'));
        $this->_mockFlash();
        $this->Trait->Flash->expects($this->never())
            ->method('success');

        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with(__d('CakeDC/Users', 'Could not associate account, please try again.'));

        $ProviderMock = $this->getMockBuilder('League\OAuth2\Client\Provider\Facebook')
            ->setMethods(['getAccessToken', 'getResourceOwner'])
            ->disableOriginalConstructor()
            ->getMock();

        $ProviderMock->expects($this->never())
            ->method('getAccessToken');

        $ProviderMock->expects($this->never())
            ->method('getResourceOwner');

        $this->Trait->expects($this->once())
            ->method('_createSocialProvider')
            ->with(
                $this->equalTo([
                    'className' => 'League\OAuth2\Client\Provider\Facebook',
                    'options' => [
                        'graphApiVersion' => 'v2.5',
                        'redirectUri' => '/auth/facebook',
                        'linkSocialUri' => '/link-social/facebook',
                        'callbackLinkSocialUri' => '/callback-link-social/facebook',
                        'clientId' => 'testclientidtestclientid',
                        'clientSecret' => 'testclientsecrettestclientsecret'
                    ]
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
    public function testCallbackLinkSocialWrongState()
    {
        Configure::write('OAuth.providers.facebook.options.clientId', 'testclientidtestclientid');
        Configure::write('OAuth.providers.facebook.options.clientSecret', 'testclientsecrettestclientsecret');

        $Table = TableRegistry::get('CakeDC/Users.Users');

        $this->Trait = $this->getMockBuilder('CakeDC\Users\Controller\Traits\LinkSocialTrait')
            ->setMethods(['dispatchEvent', 'redirect', 'set', '_createSocialProvider', 'getUsersTable', 'log'])
            ->getMockForTrait();

        $this->Trait->expects($this->any())
            ->method('getUsersTable')
            ->will($this->returnValue($Table));

        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['setConfig'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockRequestGet(true);
        $this->Trait->request->expects($this->never())
                ->method('getQuery');

        $this->Trait->request->expects($this->once())
                ->method('getQueryParams')
                ->will($this->returnValue([
                    'code' => '99999000222220',
                    'state' => 'bd393j2942789'
                ]));
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(
                $this->equalTo(['action' => 'profile'])
            );

        $this->_mockSession([
            'SocialLink' => [
                'oauth2state' => 'a393j2942789'
            ]
        ]);
        $this->_mockAuthLoggedIn();
        $this->_mockDispatchEvent(new Event('event'));
        $this->_mockFlash();
        $this->Trait->Flash->expects($this->never())
            ->method('success');

        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with(__d('CakeDC/Users', 'Could not associate account, please try again.'));

        $ProviderMock = $this->getMockBuilder('League\OAuth2\Client\Provider\Facebook')
            ->setMethods(['getAccessToken', 'getResourceOwner'])
            ->disableOriginalConstructor()
            ->getMock();

        $ProviderMock->expects($this->never())
            ->method('getAccessToken');

        $ProviderMock->expects($this->never())
            ->method('getResourceOwner');

        $this->Trait->expects($this->once())
            ->method('_createSocialProvider')
            ->with(
                $this->equalTo([
                    'className' => 'League\OAuth2\Client\Provider\Facebook',
                    'options' => [
                        'graphApiVersion' => 'v2.5',
                        'redirectUri' => '/auth/facebook',
                        'linkSocialUri' => '/link-social/facebook',
                        'callbackLinkSocialUri' => '/callback-link-social/facebook',
                        'clientId' => 'testclientidtestclientid',
                        'clientSecret' => 'testclientsecrettestclientsecret'
                    ]
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

        $Table = TableRegistry::get('CakeDC/Users.Users');

        $this->Trait = $this->getMockBuilder('CakeDC\Users\Controller\Traits\LinkSocialTrait')
            ->setMethods(['dispatchEvent', 'redirect', 'set', 'getUsersTable', 'log'])
            ->getMockForTrait();

        $this->Trait->expects($this->any())
            ->method('getUsersTable')
            ->will($this->returnValue($Table));

        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['setConfig'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockRequestGet(true);
        $this->Trait->request->expects($this->never())
                ->method('getQuery');

        $this->Trait->request->expects($this->once())
                ->method('getQueryParams')
                ->will($this->returnValue([
                    'state' => 'bd393j2942789'
                ]));
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(
                $this->equalTo(['action' => 'profile'])
            );

        $this->_mockSession([
            'SocialLink' => [
                'oauth2state' => 'a393j2942789'
            ]
        ]);
        $this->_mockAuthLoggedIn();
        $this->_mockDispatchEvent(new Event('event'));
        $this->_mockFlash();
        $this->Trait->Flash->expects($this->never())
            ->method('success');

        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with(__d('CakeDC/Users', 'Could not associate account, please try again.'));

        $this->Trait->callbackLinkSocial('facebook');
    }
}
