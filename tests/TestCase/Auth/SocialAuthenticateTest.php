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

namespace CakeDC\Users\Test\TestCase\Auth;

use CakeDC\Users\Controller\Component\UsersAuthComponent;
use CakeDC\Users\Exception\AccountNotActiveException;
use CakeDC\Users\Exception\MissingEmailException;
use CakeDC\Users\Exception\UserNotActiveException;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use ReflectionClass;

class SocialAuthenticateTest extends TestCase
{
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
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        $request = new ServerRequest();
        $response = new Response();

        $this->Table = TableRegistry::get('CakeDC/Users.Users');

        $this->Token = $this->getMockBuilder('League\OAuth2\Client\Token\AccessToken')
            ->setMethods(['getToken', 'getExpires'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setMethods(['failedSocialLogin', 'dispatchEvent'])
            ->setConstructorArgs([$request, $response])
            ->getMock();

        $this->controller->expects($this->any())
            ->method('dispatchEvent')
            ->will($this->returnValue(new Event('test')));

        $this->Request = $request;
        $this->SocialAuthenticate = $this->_getSocialAuthenticateMockMethods(['_authenticate', '_getProviderName',
                '_mapUser', '_socialLogin', 'dispatchEvent', '_validateConfig', '_getController']);

        $this->SocialAuthenticate->expects($this->any())
            ->method('_getController')
            ->will($this->returnValue($this->controller));
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown()
    {
        unset($this->SocialAuthenticate, $this->controller);
    }

    protected function _getSocialAuthenticateMock()
    {
        return $this->getMockBuilder('CakeDC\Users\Auth\SocialAuthenticate')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function _getSocialAuthenticateMockMethods($methods)
    {
        return $this->getMockBuilder('CakeDC\Users\Auth\SocialAuthenticate')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Test getUser
     *
     * @dataProvider providerGetUser
     */
    public function testGetUserAuth($rawData, $mapper)
    {
        $user = $this->Table->get('00000000-0000-0000-0000-000000000002', ['contain' => ['SocialAccounts']]);

        $this->controller->expects($this->once())
            ->method('dispatchEvent')
            ->with(UsersAuthComponent::EVENT_AFTER_REGISTER, compact('user'));

        $this->SocialAuthenticate->expects($this->once())
         ->method('_authenticate')
         ->with($this->Request)
         ->will($this->returnValue($rawData));

        $this->SocialAuthenticate->expects($this->once())
            ->method('_getProviderName')
            ->will($this->returnValue('facebook'));

        $this->SocialAuthenticate->expects($this->once())
            ->method('_mapUser')
            ->will($this->returnValue($mapper));

        $this->SocialAuthenticate->expects($this->once())
            ->method('_socialLogin')
            ->will($this->returnValue($user));

        $result = $this->SocialAuthenticate->getUser($this->Request);
        $this->assertTrue($result['active']);
        $this->assertEquals('00000000-0000-0000-0000-000000000002', $result['id']);
    }

    /**
     * Provider for getUser test method
     *
     */
    public function providerGetUser()
    {
        return [
            [
                'rawData' => [
                    'token' => 'token',
                    'id' => 'reference-2-1',
                    'name' => 'User S',
                    'first_name' => 'user',
                    'last_name' => 'second',
                    'email' => 'userSecond@example.com',
                    'cover' => [
                        'id' => 'reference-2-1'
                    ],
                    'gender' => 'female',
                    'locale' => 'en_US',
                    'link' => 'link',
                ],
                'mappedData' => [
                    'id' => 'reference-2-1',
                    'username' => null,
                    'full_name' => 'User S',
                    'first_name' => 'user',
                    'last_name' => 'second',
                    'email' => 'userSecond@example.com',
                    'link' => 'link',
                    'bio' => null,
                    'locale' => 'en_US',
                    'validated' => true,
                    'credentials' => [
                        'token' => 'token',
                        'secret' => null,
                        'expires' => 1458423682
                    ],
                    'raw' => [

                    ],
                    'provider' => 'Facebook'
                ],
            ]

        ];
    }

    /**
     * Test getUser
     *
     */
    public function testGetUserSessionData()
    {
        $user = ['username' => 'username', 'email' => 'myemail@test.com'];
        $this->SocialAuthenticate = $this->_getSocialAuthenticateMockMethods(['_authenticate',
                '_getProviderName', '_mapUser', '_touch', '_validateConfig' ]);

        $session = $this->getMockBuilder('Cake\Network\Session')
                ->setMethods(['read', 'delete'])
                ->getMock();
        $session->expects($this->once())
            ->method('read')
            ->with('Users.social')
            ->will($this->returnValue($user));

        $session->expects($this->once())
            ->method('delete')
            ->with('Users.social');

        $this->Request = $this->getMockBuilder('Cake\Network\Request')
                ->setMethods(['session'])
                ->getMock();
        $this->Request->expects($this->any())
            ->method('session')
            ->will($this->returnValue($session));

        $this->SocialAuthenticate->expects($this->once())
            ->method('_touch')
            ->will($this->returnValue($user));

        $this->SocialAuthenticate->getUser($this->Request);
    }

    /**
     * Test getUser
     *
     * @dataProvider providerGetUser
     */
    public function testGetUserNotEmailProvided($rawData, $mapper)
    {
        $this->SocialAuthenticate->expects($this->once())
            ->method('_authenticate')
            ->with($this->Request)
            ->will($this->returnValue($rawData));

        $this->SocialAuthenticate->expects($this->once())
            ->method('_getProviderName')
            ->will($this->returnValue('facebook'));

        $this->SocialAuthenticate->expects($this->once())
            ->method('_mapUser')
            ->will($this->returnValue($mapper));

        $this->SocialAuthenticate->expects($this->once())
            ->method('_socialLogin')
            ->will($this->throwException(new MissingEmailException('missing email')));

        $this->controller->expects($this->once())
            ->method('dispatchEvent')
            ->with(UsersAuthComponent::EVENT_FAILED_SOCIAL_LOGIN);

        $this->controller->expects($this->once())
            ->method('failedSocialLogin');

        $this->SocialAuthenticate->getUser($this->Request);
    }

    /**
     * Test getUser
     *
     * @dataProvider providerGetUser
     */
    public function testGetUserNotActive($rawData, $mapper)
    {
        $this->SocialAuthenticate->expects($this->once())
            ->method('_authenticate')
            ->with($this->Request)
            ->will($this->returnValue($rawData));

        $this->SocialAuthenticate->expects($this->once())
            ->method('_getProviderName')
            ->will($this->returnValue('facebook'));

        $this->SocialAuthenticate->expects($this->once())
            ->method('_mapUser')
            ->will($this->returnValue($mapper));

        $this->SocialAuthenticate->expects($this->once())
            ->method('_socialLogin')
            ->will($this->throwException(new UserNotActiveException('user not active')));

        $this->SocialAuthenticate->getUser($this->Request);
    }

    /**
     * Test getUser
     *
     * @dataProvider providerGetUser
     */
    public function testGetUserNotActiveAccount($rawData, $mapper)
    {
        $this->SocialAuthenticate->expects($this->once())
            ->method('_authenticate')
            ->with($this->Request)
            ->will($this->returnValue($rawData));

        $this->SocialAuthenticate->expects($this->once())
            ->method('_getProviderName')
            ->will($this->returnValue('facebook'));

        $this->SocialAuthenticate->expects($this->once())
            ->method('_mapUser')
            ->will($this->returnValue($mapper));

        $this->SocialAuthenticate->expects($this->once())
            ->method('_socialLogin')
            ->will($this->throwException(new AccountNotActiveException('user not active')));

        $this->SocialAuthenticate->getUser($this->Request);
    }

    /**
     * Test getUser
     *
     * @dataProvider providerTwitter
     */
    public function testGetUserNotEmailProvidedTwitter($rawData, $mapper)
    {
        $this->SocialAuthenticate->expects($this->once())
            ->method('_authenticate')
            ->with($this->Request)
            ->will($this->returnValue($rawData));

        $this->SocialAuthenticate->expects($this->once())
            ->method('_getProviderName')
            ->will($this->returnValue('twitter'));

        $this->SocialAuthenticate->expects($this->once())
            ->method('_mapUser')
            ->will($this->returnValue($mapper));

        $this->SocialAuthenticate->expects($this->once())
            ->method('_socialLogin')
            ->will($this->throwException(new MissingEmailException('missing email')));

        $this->SocialAuthenticate->getUser($this->Request);
    }

    /**
     * Provider for getUser test method
     *
     */
    public function providerTwitter()
    {
        return [
            [
                'rawData' => [
                    'token' => 'token',
                    'id' => 'reference-2-1',
                    'name' => 'User S',
                    'first_name' => 'user',
                    'last_name' => 'second',
                    'email' => 'userSecond@example.com',
                    'cover' => [
                        'id' => 'reference-2-1'
                    ],
                    'gender' => 'female',
                    'locale' => 'en_US',
                    'link' => 'link',
                ],
                'mappedData' => [
                    'id' => 'reference-2-1',
                    'username' => null,
                    'full_name' => 'User S',
                    'first_name' => 'user',
                    'last_name' => 'second',
                    'email' => 'userSecond@example.com',
                    'link' => 'link',
                    'bio' => null,
                    'locale' => 'en_US',
                    'validated' => true,
                    'credentials' => [
                        'token' => 'token',
                        'secret' => null,
                        'expires' => 1458423682
                    ],
                    'raw' => [

                    ],
                    'provider' => 'Twitter'
                ],
            ]

        ];
    }

    /**
     * Test _socialLogin
     *
     * @dataProvider providerMapper
     */
    public function testSocialLogin()
    {
        $this->SocialAuthenticate = $this->_getSocialAuthenticateMock();

        $reflectedClass = new ReflectionClass($this->SocialAuthenticate);
        $socialLogin = $reflectedClass->getMethod('_socialLogin');
        $socialLogin->setAccessible(true);
        $data = [
            'id' => 'reference-2-1',
            'provider' => 'Facebook'
        ];
        $result = $socialLogin->invoke($this->SocialAuthenticate, $data);
        $this->assertEquals($result->id, '00000000-0000-0000-0000-000000000002');
        $this->assertTrue($result->active);
    }

    /**
     * Test _mapUser
     *
     * @dataProvider providerMapper
     */
    public function testMapUser($data, $mappedData)
    {
        $data['token'] = $this->Token;
        $this->SocialAuthenticate = $this->_getSocialAuthenticateMock();

        $reflectedClass = new ReflectionClass($this->SocialAuthenticate);
        $mapUser = $reflectedClass->getMethod('_mapUser');
        $mapUser->setAccessible(true);

        $this->Token->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue('token'));

        $this->Token->expects($this->once())
            ->method('getExpires')
            ->will($this->returnValue(1458510952));

        $result = $mapUser->invoke($this->SocialAuthenticate, 'Facebook', $data);
        unset($result['raw']);
        $this->assertEquals($mappedData, $result);
    }

    /**
     * Provider for _mapUser test method
     *
     */
    public function providerMapper()
    {
        return [
                [
                'rawData' => [
                    'id' => 'my-facebook-id',
                    'name' => 'My name.',
                    'first_name' => 'My first name',
                    'last_name' => 'My lastname.',
                    'email' => 'myemail@example.com',
                    'gender' => 'female',
                    'locale' => 'en_US',
                    'link' => 'https://www.facebook.com/app_scoped_user_id/my-facebook-id/',
                ],
                'mappedData' => [
                    'id' => 'my-facebook-id',
                    'username' => null,
                    'full_name' => 'My name.',
                    'first_name' => 'My first name',
                    'last_name' => 'My lastname.',
                    'email' => 'myemail@example.com',
                    'avatar' => 'https://graph.facebook.com/my-facebook-id/picture?type=large',
                    'gender' => 'female',
                    'link' => 'https://www.facebook.com/app_scoped_user_id/my-facebook-id/',
                    'bio' => null,
                    'locale' => 'en_US',
                    'validated' => true,
                    'credentials' => [
                        'token' => 'token',
                        'secret' => null,
                        'expires' => (int)1458510952
                    ],
                    'provider' => 'Facebook'
                ],
                ]

        ];
    }

    /**
     * Test _mapUser
     *
     * @expectedException CakeDC\Users\Exception\MissingProviderException
     */
    public function testMapUserException()
    {
        $data = [];
        $this->SocialAuthenticate = $this->_getSocialAuthenticateMock();

        $reflectedClass = new ReflectionClass($this->SocialAuthenticate);
        $mapUser = $reflectedClass->getMethod('_mapUser');
        $mapUser->setAccessible(true);
        $mapUser->invoke($this->SocialAuthenticate, null, $data);
    }

    /**
     * Provider for normalizeConfig test method
     *
     * @dataProvider providers
     */
    public function testNormalizeConfig($data, $oauth2, $callTimes, $enabledNoOAuth2Provider)
    {
        Configure::write('OAuth2', $oauth2);
        $this->SocialAuthenticate = $this->_getSocialAuthenticateMockMethods(['_authenticate',
            '_getProviderName', '_mapUser', '_touch', '_validateConfig', '_normalizeConfig' ]);

        $this->SocialAuthenticate->expects($this->exactly($callTimes))
            ->method('_normalizeConfig');

        $this->SocialAuthenticate->normalizeConfig($data, $enabledNoOAuth2Provider);
    }

    /**
     * Test normalizeConfig
     *
     * @expectedException CakeDC\Users\Auth\Exception\MissingProviderConfigurationException
     */
    public function testNormalizeConfigException()
    {
        $this->SocialAuthenticate->normalizeConfig([]);
    }

    /**
     * Provider for normalizeConfig test method
     *
     */
    public function providers()
    {
        return [
            [
                [
                    'providers' => [
                        'facebook' => [
                            'className' => 'League\OAuth2\Client\Provider\Facebook',
                        ],
                        'instagram' => [
                            'className' => 'League\OAuth2\Client\Provider\Instagram',
                        ]
                    ],

                ],
                [
                    'providers' => [
                        'facebook' => [
                            'className' => 'League\OAuth2\Client\Provider\Facebook',
                        ],
                        'instagram' => [
                            'className' => 'League\OAuth2\Client\Provider\Instagram',
                        ]
                    ]
                ],
                2,
                false
            ],
            [
                [
                    'providers' => [
                        'facebook' => [
                            'className' => 'League\OAuth2\Client\Provider\Facebook',
                        ],
                    ],

                ],
                [
                    'providers' => [
                        'facebook' => [
                            'className' => 'League\OAuth2\Client\Provider\Facebook',
                        ],
                    ]
                ],
                1,
                false
            ],
            [
                [
                    'providers' => [
                        'facebook' => [
                            'className' => 'League\OAuth2\Client\Provider\Facebook',
                        ],
                    ],

                ],
                [
                    'providers' => [
                        'instagram' => [
                            'className' => 'League\OAuth2\Client\Provider\Instagram',
                        ]
                    ]
                ],
                2,
                false
            ],
            [
                [],
                [],
                0,
                true
            ]
        ];
    }
}
