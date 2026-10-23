<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Controller\Traits;

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Http\ServerRequestFactory;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use CakeDC\Users\Model\Behavior\LinkSocialBehavior;
use CakeDC\Users\Model\Table\UsersTable;
use Laminas\Diactoros\Uri;
use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Token\AccessToken;

class LinkSocialTraitTest extends BaseTrait
{
    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = [
        'plugin.CakeDC/Users.SocialAccounts',
        'plugin.CakeDC/Users.Users',
    ];

    /**
     * @var \League\OAuth2\Client\Provider\Facebook
     */
    public $Provider;
    /**
     * @var \CakeDC\Users\Controller\UsersController
     */
    public $Trait;

    /**
     * setup
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->traitClassName = 'CakeDC\Users\Controller\UsersController';
        $this->traitMockMethods = ['dispatchEvent', 'redirect', 'getUsersTable', 'set'];
        $this->traitMockAddMethods = ['isStopped'];
        $this->skipUsersMock = true;

        parent::setUp();
        $request = new ServerRequest();

        $this->Trait->setRequest($request);

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
        ])->onlyMethods([
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
    }

    /**
     * test linkSocial method
     *
     * @return void
     */
    public function testLinkSocialHappy(): void
    {
        Configure::write('OAuth.providers.facebook.options.clientId', 'testclientidtestclientid');
        Configure::write('OAuth.providers.facebook.options.clientSecret', 'testclientsecrettestclientsecret');

        $this->Trait->setRequest(ServerRequestFactory::fromGlobals());
        $this->Trait->getRequest()->getSession()->write('oauth2state', '__TEST_STATE__');
        $uri = new Uri('/callback-link-social/facebook');

        $this->Trait->setRequest($this->Trait->getRequest()->withUri($uri));
        $this->Trait->setRequest($this->Trait->getRequest()->withQueryParams([
            'code' => 'ZPO9972j3092304230',
            'state' => '__TEST_STATE__',
        ]));
        $this->Trait->setRequest($this->Trait->getRequest()->withAttribute('params', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'linkSocial',
            'provider' => 'facebook',
        ]));

        $this->mockAuthLoggedIn();
        $this->mockDispatchEvent(new Event('event'));
        $flash = $this->mockFlash();

        $this->Provider->expects($this->atLeastOnce())
            ->method('getState')
            ->willReturn('_NEW_STATE_');

        $this->Provider->expects($this->atLeastOnce())
            ->method('getAuthorizationUrl')
            ->willReturn('http://facebook.com/redirect/url');

        $flash->expects('set')->never();

        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with($this->equalTo('http://facebook.com/redirect/url'))
            ->willReturn(new Response());

        $this->Trait->linkSocial('facebook');
    }

    /**
     * test
     *
     * @return void
     */
    public function testCallbackLinkSocialHappy(): void
    {
        Configure::write('OAuth.providers.facebook.options.clientId', 'testclientidtestclientid');
        Configure::write('OAuth.providers.facebook.options.clientSecret', 'testclientsecrettestclientsecret');

        $Table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');

        $Token = new AccessToken([
            'access_token' => 'test-token',
            'expires' => 1490988496,
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
            'link' => 'facebook-link-15579',
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

        $this->Provider->expects($this->atLeastOnce())
            ->method('getAccessToken')
            ->with(
                $this->equalTo('authorization_code'),
                $this->equalTo(['code' => 'ZPO9972j3092304230']),
            )
            ->willReturn($Token);

        $this->Provider->expects($this->atLeastOnce())
            ->method('getResourceOwner')
            ->with(
                $this->equalTo($Token),
            )
            ->willReturn($user);

        $this->Trait->setRequest(ServerRequestFactory::fromGlobals());
        $this->Trait->getRequest()->getSession()->write('oauth2state', '__TEST_STATE__');
        $uri = new Uri('/callback-link-social/facebook');

        $this->Trait->setRequest($this->Trait->getRequest()->withUri($uri));
        $this->Trait->setRequest($this->Trait->getRequest()->withQueryParams([
            'code' => 'ZPO9972j3092304230',
            'state' => '__TEST_STATE__',
        ]));
        $this->Trait->setRequest($this->Trait->getRequest()->withAttribute('params', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'linkSocial',
            'provider' => 'facebook',
        ]));

        $this->Trait->expects($this->atLeastOnce())
            ->method('getUsersTable')
            ->willReturn($Table);

        $this->mockAuthLoggedIn();
        $flash = $this->mockFlash();
        $flash->expects('set')
            ->with(__d('cake_d_c/users', 'Social account was associated.'), ['element' => 'success']);

        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['action' => 'profile'])
            ->willReturn(new Response());

        $this->Trait->callbackLinkSocial('facebook');

        $actual = $Table->SocialAccounts->find('all')->where(['reference' => '9999911112255'])->firstOrFail();

        $expiresTime = new DateTime();
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
            'active' => true,
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
    public function testCallbackLinkSocialWithValidationErrors(): void
    {
        Configure::write('OAuth.providers.facebook.options.clientId', 'testclientidtestclientid');
        Configure::write('OAuth.providers.facebook.options.clientSecret', 'testclientsecrettestclientsecret');
        $user = TableRegistry::getTableLocator()->get('CakeDC/Users.Users')->get('00000000-0000-0000-0000-000000000001');
        $user->setErrors([
            'social_accounts' => [
                '_existsIn' => __d('cake_d_c/users', 'Social account already associated to another user'),
            ],
        ]);
        $Table = $this->getMockBuilder(UsersTable::class)
            ->setConstructorArgs([['alias' => 'Users', 'connection' => ConnectionManager::get('test')]])
            ->onlyMethods(['newEntity', 'get', 'getBehavior'])
            ->getMock();
        $behavior = $this->getMockBuilder(LinkSocialBehavior::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['linkSocialAccount'])
            ->getMock();

        $Table->setAlias('Users');

        $Table->expects($this->once())
            ->method('get')
            ->willReturn($user);

        $Table->expects($this->once())
            ->method('getBehavior')
            ->with('LinkSocial')
            ->willReturn($behavior);
        $behavior->expects($this->once())
            ->method('linkSocialAccount')
            ->willReturn($user);

        $Token = new AccessToken([
            'access_token' => 'test-token',
            'expires' => 1490988496,
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
            'link' => 'facebook-link-15579',
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

        $this->Provider->expects($this->atLeastOnce())
            ->method('getAccessToken')
            ->with(
                $this->equalTo('authorization_code'),
                $this->equalTo(['code' => 'ZPO9972j3092304230']),
            )
            ->willReturn($Token);

        $this->Provider->expects($this->atLeastOnce())
            ->method('getResourceOwner')
            ->with(
                $this->equalTo($Token),
            )
            ->willReturn($user);

        $this->Trait->expects($this->atLeastOnce())
            ->method('getUsersTable')
            ->willReturn($Table);

        $this->Trait->setRequest(ServerRequestFactory::fromGlobals());
        $this->Trait->getRequest()->getSession()->write('oauth2state', '__TEST_STATE__');
        $uri = new Uri('/callback-link-social/facebook');

        $this->Trait->setRequest($this->Trait->getRequest()->withUri($uri));
        $this->Trait->setRequest($this->Trait->getRequest()->withQueryParams([
            'code' => 'ZPO9972j3092304230',
            'state' => '__TEST_STATE__',
        ]));
        $this->Trait->setRequest($this->Trait->getRequest()->withAttribute('params', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'linkSocial',
            'provider' => 'facebook',
        ]));

        $this->mockAuthLoggedIn();
        $flash = $this->mockFlash();
        $flash->expects('set')
            ->with(__d('cake_d_c/users', 'Could not associate account, please try again.'), ['element' => 'error']);

        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['action' => 'profile'])
            ->willReturn(new Response());

        $this->Trait->callbackLinkSocial('facebook');

        $actual = $Table->SocialAccounts->exists(['reference' => '9999911112255']);
        $this->assertFalse($actual);
    }

    /**
     * test
     *
     * @return void
     */
    public function testCallbackLinkSocialQueryHasErrors(): void
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

        $this->Trait->setRequest(ServerRequestFactory::fromGlobals());
        $this->Trait->getRequest()->getSession()->write('oauth2state', '__TEST_STATE__');
        $uri = new Uri('/callback-link-social/facebook');

        $this->Trait->setRequest($this->Trait->getRequest()->withUri($uri));
        $this->Trait->setRequest($this->Trait->getRequest()->withAttribute('params', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'linkSocial',
            'provider' => 'facebook',
        ]));

        $this->Trait->expects($this->never())
            ->method('getUsersTable');
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with($this->equalTo([
                'action' => 'profile',
            ]))
            ->willReturn(new Response());
        $this->mockAuthLoggedIn();
        $flash = $this->mockFlash();
        $flash->expects('set')
            ->with(__d('cake_d_c/users', 'Could not associate account, please try again.'), ['element' => 'error']);

        $result = $this->Trait->callbackLinkSocial('facebook');
        $this->assertInstanceOf(Response::class, $result);
    }

    /**
     * test
     *
     * @return void
     */
    public function testCallbackLinkSocialUnknownProvider(): void
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

        $this->Trait->setRequest(ServerRequestFactory::fromGlobals());
        $this->Trait->getRequest()->getSession()->write('oauth2state', '__TEST_STATE__');
        $uri = new Uri('/callback-link-social/facebook');

        $this->Trait->setRequest($this->Trait->getRequest()->withUri($uri));
        $this->Trait->setRequest($this->Trait->getRequest()->withAttribute('params', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'linkSocial',
            'provider' => 'unknown',
        ]));

        $this->Trait->expects($this->never())
            ->method('getUsersTable');

        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with($this->equalTo([
                'action' => 'profile',
            ]))
            ->willReturn(new Response());
        $this->mockAuthLoggedIn();
        $flash = $this->mockFlash();
        $flash->expects('set')
            ->with(__d('cake_d_c/users', 'Could not associate account, please try again.'), ['element' => 'error']);

        $result = $this->Trait->callbackLinkSocial('unknown');
        $this->assertInstanceOf(Response::class, $result);
    }
}
