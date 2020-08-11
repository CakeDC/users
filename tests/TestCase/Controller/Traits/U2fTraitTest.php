<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Controller\Traits;

use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use CakeDC\Auth\Authentication\DefaultU2fAuthenticationChecker;
use CakeDC\Users\Model\Entity\User;
use u2flib_server\RegisterRequest;
use u2flib_server\Registration;
use u2flib_server\U2F;

/**
 * Class U2fTraitTest
 *
 * @package App\Test\TestCase\Controller\Traits
 */
class U2fTraitTest extends BaseTraitTest
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.Users',
    ];

    /**
     * setup
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->traitClassName = 'CakeDC\Users\Controller\UsersController';
        $this->traitMockMethods = ['dispatchEvent', 'isStopped', 'redirect', 'getUsersTable', 'set', 'createU2fLib', 'getData', 'getU2fAuthenticationChecker'];

        parent::setUp();

        $this->Trait->expects($this->any())
            ->method('getU2fAuthenticationChecker')
            ->willReturn(new DefaultU2fAuthenticationChecker());

        $request = new ServerRequest();
        $this->Trait->setRequest($request);
        Configure::write('U2f.enabled', true);
    }

    /**
     * Mock session and mock session attributes
     *
     * @return \Cake\Http\Session
     */
    protected function _mockSession($attributes)
    {
        $session = new \Cake\Http\Session();

        foreach ($attributes as $field => $value) {
            $session->write($field, $value);
        }

        $this->Trait
            ->getRequest()
            ->expects($this->any())
            ->method('getSession')
            ->willReturn($session);

        return $session;
    }

    /**
     * Data provider for testU2User
     *
     * @return array
     */
    public function dataProviderU2User()
    {
        $empty = [];
        $withRegistration = new User([
            'id' => '00000000-0000-0000-0000-000000000001',
            'username' => 'user-1',
        ]);
        $withWhoutRegistration = new User([
            'id' => '00000000-0000-0000-0000-000000000002',
            'username' => 'user-2',
        ]);

        return [
            [$empty, ['action' => 'login']],
            [$withWhoutRegistration, ['action' => 'u2fRegister']],
            [$withRegistration, ['action' => 'u2fAuthenticate']],
        ];
    }

    /**
     * Test u2f method
     *
     * @param array $userData session user data
     * @param mixed $redirect expetected redirect
     * @dataProvider dataProviderU2User
     * @return void
     */
    public function testU2fCustomUser($userData, $redirect)
    {
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['getSession', 'is'])
            ->getMock();
        $this->Trait->setRequest($request);
        $request->expects($this->any())
            ->method('is')
            ->with(
                $this->equalTo('ssl')
            )->will($this->returnValue(true));
        $response = new Response([
            'body' => (string)time(),
        ]);
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(
                $this->equalTo($redirect)
            )->will($this->returnValue($response));
        $this->_mockSession([
            'U2f.User' => $userData,
        ]);
        $actual = $this->Trait->u2f();
        $this->assertSame($response, $actual);
    }

    /**
     * Test u2fRegister method
     *
     * @return void
     */
    public function testU2fRegisterOkay()
    {
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['getSession', 'is'])
            ->getMock();
        $this->Trait->setRequest($request);
        $request->expects($this->once())
            ->method('is')
            ->with(
                $this->equalTo('ssl')
            )->will($this->returnValue(true));

        $u2fLib = $this->getMockBuilder(U2F::class)
            ->setConstructorArgs(['https://localhost'])
            ->setMethods(['getRegisterData'])
            ->getMock();

        $registerRequest = new RegisterRequest('sample chalange', 'https://localhost');
        $signs = [
            ['fake' => new \stdClass()],
            ['fake2' => new \stdClass()],
        ];
        $u2fLib->expects($this->once())
            ->method('getRegisterData')
            ->will($this->returnValue([$registerRequest, $signs]));

        $this->Trait->expects($this->once())
            ->method('createU2fLib')
            ->will($this->returnValue($u2fLib));
        $this->Trait->expects($this->once())
            ->method('set')
            ->with(
                $this->equalTo([
                'registerRequest' => $registerRequest,
                'signs' => $signs,
                ])
            );
        $this->Trait->expects($this->never())
            ->method('redirect');

        $this->_mockSession([
            'U2f.User' => new User([
                'id' => '00000000-0000-0000-0000-000000000002',
                'username' => 'user-2',
            ]),
        ]);
        $actual = $this->Trait->u2fRegister();
        $this->assertNull($actual);
        $actual = $this->Trait->getRequest()->getSession()->read('U2f.registerRequest');
        $expected = json_encode($registerRequest);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Data provider for testU2fRegisterRedirect
     *
     * @return array
     */
    public function dataProviderU2fRegisterRedirect()
    {
        $empty = [];
        $withRegistration = new User([
            'id' => '00000000-0000-0000-0000-000000000001',
            'username' => 'user-1',
        ]);

        return [
            [$empty, ['action' => 'login']],
            [$withRegistration, ['action' => 'u2fAuthenticate']],
        ];
    }

    /**
     * Test u2fRegister method
     *
     * @param array $userData session user data
     * @param mixed $redirect expetected redirect
     * @dataProvider dataProviderU2fRegisterRedirect
     * @return void
     */
    public function testU2fRegisterRedirect($userData, $redirect)
    {
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['getSession', 'is'])
            ->getMock();
        $this->Trait->setRequest($request);
        $request->expects($this->any())
            ->method('is')
            ->with(
                $this->equalTo('ssl')
            )->will($this->returnValue(true));
        $this->Trait->expects($this->never())
            ->method('createU2fLib');

        $this->Trait->expects($this->never())
            ->method('set');

        $this->_mockSession([
            'U2f.User' => $userData,
        ]);
        $response = new Response([
            'body' => (string)time(),
        ]);
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(
                $this->equalTo($redirect)
            )->will($this->returnValue($response));

        $actual = $this->Trait->u2fRegister();
        $this->assertSame($response, $actual);
        $actual = $this->Trait->getRequest()->getSession()->read('U2f.registerRequest');
        $expected = null;
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test u2fRegister method
     *
     * @return void
     */
    public function testU2fRegisterFinishOkay()
    {
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['getSession', 'is', 'getData'])
            ->getMock();
        $this->Trait->setRequest($request);
        $request->expects($this->once())
            ->method('is')
            ->with(
                $this->equalTo('ssl')
            )->will($this->returnValue(true));

        $u2fLib = $this->getMockBuilder(U2F::class)
            ->setConstructorArgs(['https://localhost'])
            ->setMethods(['doRegister'])
            ->getMock();

        $registerRequest = new RegisterRequest('sample chalange', 'https://localhost');
        $registerRequest = json_decode(json_encode($registerRequest));
        $signs = [
            ['fake' => new \stdClass()],
            ['fake2' => new \stdClass()],
        ];
        $registerResponse = json_decode(json_encode([
            'fakeA' => 'fakevaluea',
            'fakeB' => 'fakevalueb',
        ]));
        $registration = new Registration();
        $registration->certificate = 'user registration cert ' . time();
        $registration->counter = 1;
        $registration->publicKey = 'pub skska08u90234230990';
        $registration->keyHandle = 'hahdofa02390423udu9ma0dumfá0dsufm2um9432uu903u923';

        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->with($this->equalTo('registerResponse'))
            ->will($this->returnValue(json_encode($registerResponse)));
        $this->_mockSession([
            'U2f' => [
                'User' => new User([
                    'id' => '00000000-0000-0000-0000-000000000002',
                    'username' => 'user-2',
                ]),
                'registerRequest' => json_encode($registerRequest),
            ],
        ]);
        $u2fLib->expects($this->once())
            ->method('doRegister')
            ->with(
                $this->equalTo($registerRequest),
                $this->equalTo($registerResponse)
            )
            ->will($this->returnValue($registration));

        $this->Trait->expects($this->once())
            ->method('createU2fLib')
            ->will($this->returnValue($u2fLib));

        $actual = $this->Trait->getRequest()->getSession()->read('U2f');
        $this->assertNotNull($actual);

        $response = new Response();
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(
                $this->equalTo([
                    'action' => 'u2fAuthenticate',
                ])
            )->will($this->returnValue($response));

        $actual = $this->Trait->u2fRegisterFinish();
        $this->assertSame($response, $actual);
        $actual = $this->Trait->getRequest()->getSession()->read('U2f');
        $this->assertEquals('00000000-0000-0000-0000-000000000002', $actual['User']['id']);
        $this->assertEquals('user-2', $actual['User']['username']);
        $this->assertNotEmpty($actual['User']['additional_data']);
        $this->assertNotEmpty($actual['User']['additional_data']['u2f_registration']);

        $saveUser = TableRegistry::getTableLocator()
            ->get('CakeDC/Users.Users')
            ->get('00000000-0000-0000-0000-000000000002');

        $savedRegistration = $saveUser->u2f_registration;
        $this->assertNotNull($savedRegistration);
        $this->assertEquals(json_encode($registration), json_encode($savedRegistration));

        $registration = new Registration();
        $registration->certificate = 'user registration cert ' . time();
        $registration->counter = 1;
        $registration->publicKey = 'pub skska08u90234230990';
        $registration->keyHandle = 'hahdofa02390423udu9ma0dumfá0dsufm2um9432uu903u923';
    }

    /**
     * Test u2fRegister method
     *
     * @return void
     */
    public function testU2fRegisterFinishException()
    {
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['getSession', 'is', 'getData'])
            ->getMock();
        $this->Trait->setRequest($request);
        $request->expects($this->once())
            ->method('is')
            ->with(
                $this->equalTo('ssl')
            )->will($this->returnValue(true));

        $u2fLib = $this->getMockBuilder(U2F::class)
            ->setConstructorArgs(['https://localhost'])
            ->setMethods(['doRegister'])
            ->getMock();

        $registerRequest = new RegisterRequest('sample chalange', 'https://localhost');
        $registerRequest = json_decode(json_encode($registerRequest));
        $registerResponse = json_decode(json_encode([
            'fakeA' => 'fakevaluea',
            'fakeB' => 'fakevalueb',
        ]));
        $registration = new Registration();
        $registration->certificate = 'user registration cert ' . time();
        $registration->counter = 1;
        $registration->publicKey = 'pub skska08u90234230990';
        $registration->keyHandle = 'hahdofa02390423udu9ma0dumfá0dsufm2um9432uu903u923';

        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->with($this->equalTo('registerResponse'))
            ->will($this->returnValue(json_encode($registerResponse)));
        $this->_mockSession([
            'U2f' => [
                'User' => new User([
                    'id' => '00000000-0000-0000-0000-000000000002',
                    'username' => 'user-2',
                ]),
                'registerRequest' => json_encode($registerRequest),
            ],
        ]);
        $u2fLib->expects($this->once())
            ->method('doRegister')
            ->with(
                $this->equalTo($registerRequest),
                $this->equalTo($registerResponse)
            )
            ->will($this->throwException(new \Exception('Invalid request')));

        $this->Trait->expects($this->once())
            ->method('createU2fLib')
            ->will($this->returnValue($u2fLib));

        $actual = $this->Trait->getRequest()->getSession()->read('U2f');
        $this->assertNotNull($actual);

        $response = new Response();
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(
                $this->equalTo([
                    'action' => 'u2fRegister',
                ])
            )->will($this->returnValue($response));

        $actual = $this->Trait->u2fRegisterFinish();
        $this->assertSame($response, $actual);
        $actual = $this->Trait->getRequest()->getSession()->read('U2f');
        $this->assertEquals(
            [
                'User' => new User([
                    'id' => '00000000-0000-0000-0000-000000000002',
                    'username' => 'user-2',
                ]),
                ],
            $actual
        );

        $saveUser = TableRegistry::getTableLocator()
            ->get('CakeDC/Users.Users')
            ->get('00000000-0000-0000-0000-000000000002');

        $savedRegistration = $saveUser->u2f_registration;
        $this->assertNull($savedRegistration);

        $registration = new Registration();
        $registration->certificate = 'user registration cert ' . time();
        $registration->counter = 1;
        $registration->publicKey = 'pub skska08u90234230990';
        $registration->keyHandle = 'hahdofa02390423udu9ma0dumfá0dsufm2um9432uu903u923';
    }

    /**
     * Data provider for testU2fAuthenticateRedirectCustomUser
     *
     * @return array
     */
    public function dataProviderU2fAuthenticateRedirectCustomUser()
    {
        $empty = [];
        $withWhoutRegistration = new User([
            'id' => '00000000-0000-0000-0000-000000000002',
            'username' => 'user-2',
        ]);

        return [
            [$empty, ['action' => 'login']],
            [$withWhoutRegistration, ['action' => 'u2fRegister']],
        ];
    }

    /**
     * Test u2fAuthenticate method redirect cases
     *
     * @param array $userData session user data
     * @param mixed $redirect expetected redirect
     * @dataProvider dataProviderU2fAuthenticateRedirectCustomUser
     * @return void
     */
    public function testU2fAuthenticateRedirectCustomUser($userData, $redirect)
    {
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['getSession', 'is'])
            ->getMock();
        $this->Trait->setRequest($request);
        $request->expects($this->any())
            ->method('is')
            ->with(
                $this->equalTo('ssl')
            )->will($this->returnValue(true));
        $response = new Response([
            'body' => (string)time(),
        ]);
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(
                $this->equalTo($redirect)
            )->will($this->returnValue($response));
        $this->_mockSession([
            'U2f.User' => $userData,
        ]);
        $actual = $this->Trait->u2fAuthenticate();
        $this->assertSame($response, $actual);
    }

    /**
     * Test u2fAuthenticate method
     *
     * @return void
     */
    public function testU2fAuthenticate()
    {
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['getSession', 'is'])
            ->getMock();
        $this->Trait->setRequest($request);
        $request->expects($this->once())
            ->method('is')
            ->with(
                $this->equalTo('ssl')
            )->will($this->returnValue(true));

        $u2fLib = $this->getMockBuilder(U2F::class)
            ->setConstructorArgs(['https://localhost'])
            ->setMethods(['getAuthenticateData'])
            ->getMock();

        $signs = [
            ['fake' => new \stdClass()],
            ['fake2' => new \stdClass()],
        ];
        $reg1 = [
            'keyHandle' => 'fake key handle',
            'publicKey' => 'afdoaj0-23u423-ad ujsf-as8-0-afsd',
            'certificate' => '23jdsfoasdj0f9sa082304823423',
            'counter' => 1,
        ];
        $registrations = [
            (object)$reg1,
        ];
        $u2fLib->expects($this->once())
            ->method('getAuthenticateData')
            ->with(
                $this->equalTo($registrations)
            )
            ->will($this->returnValue($signs));

        $this->Trait->expects($this->once())
            ->method('createU2fLib')
            ->will($this->returnValue($u2fLib));
        $this->Trait->expects($this->once())
            ->method('set')
            ->with(
                $this->equalTo([
                    'authenticateRequest' => $signs,
                ])
            );
        $this->Trait->expects($this->never())
            ->method('redirect');

        $this->_mockSession([
            'U2f.User' => new User([
                'id' => '00000000-0000-0000-0000-000000000001',
                'username' => 'user-1',
            ]),
        ]);
        $actual = $this->Trait->u2fAuthenticate();
        $this->assertNull($actual);
        $actual = $this->Trait->getRequest()->getSession()->read('U2f.authenticateRequest');
        $expected = json_encode($signs);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test u2fAuthenticateFinish method
     *
     * @return void
     */
    public function testU2fAutheticateFinishOkay()
    {
        $user = TableRegistry::getTableLocator()
            ->get('CakeDC/Users.Users')
            ->get('00000000-0000-0000-0000-000000000001');
        $this->assertNotNull($user->u2f_registration);

        $registration = $user->u2f_registration;
        $registrationEntityResult = new Registration();
        $registrationEntityResult->keyHandle = $registration->keyHandle;
        $registrationEntityResult->publicKey = $registration->publicKey;
        $registrationEntityResult->counter = $registration->counter + 1;
        $registrationEntityResult->certificate = $registration->certificate;

        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['getSession', 'is', 'getData'])
            ->getMock();
        $this->Trait->setRequest($request);
        $request->expects($this->once())
            ->method('is')
            ->with(
                $this->equalTo('ssl')
            )->will($this->returnValue(true));
        $u2fLib = $this->getMockBuilder(U2F::class)
            ->setConstructorArgs(['https://localhost'])
            ->setMethods(['doAuthenticate'])
            ->getMock();

        $signs = json_decode(json_encode([
            ['fake' => new \stdClass()],
            ['fake2' => new \stdClass()],
        ]));
        $authenticateResponse = json_decode(json_encode([
            'fakeA' => 'fakevaluea',
            'fakeB' => 'fakevalueb',
        ]));

        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->with($this->equalTo('authenticateResponse'))
            ->will($this->returnValue(json_encode($authenticateResponse)));
        $this->_mockSession([
            'U2f' => [
                'User' => new User([
                    'id' => '00000000-0000-0000-0000-000000000001',
                    'username' => 'user-1',
                ]),
                'authenticateRequest' => json_encode($signs),
            ],
        ]);

        $u2fLib->expects($this->once())
            ->method('doAuthenticate')
            ->with(
                $this->equalTo($signs),
                $this->equalTo([$registration]),
                $this->equalTo($authenticateResponse)
            )
            ->will($this->returnValue($registrationEntityResult));

        $this->Trait->expects($this->once())
            ->method('createU2fLib')
            ->will($this->returnValue($u2fLib));

        $actual = $this->Trait->getRequest()->getSession()->read('U2f');
        $this->assertNotNull($actual);

        $response = new Response();
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with([
                'plugin' => 'CakeDC/Users',
                'controller' => 'Users',
                'action' => 'login',
                'prefix' => false,
            ])->will($this->returnValue($response));

        $actual = $this->Trait->u2fAuthenticateFinish();
        $this->assertSame($response, $actual);
        $actual = $this->Trait->getRequest()->getSession()->read('U2f');
        $this->assertNull($actual);

        $updatedEntity = TableRegistry::getTableLocator()
            ->get('CakeDC/Users.Users')
            ->get($user['id'])
            ->u2f_registration;

        $this->assertEquals($registrationEntityResult->counter, $updatedEntity->counter);
    }

    /**
     * Test u2fAuthenticateFinish method with exception
     *
     * @return void
     */
    public function testU2fAutheticateFinishWithException()
    {
        $saveUser = TableRegistry::getTableLocator()
            ->get('CakeDC/Users.Users')
            ->get('00000000-0000-0000-0000-000000000001');

        $savedRegistration = $saveUser->u2f_registration;
        $this->assertNotNull($savedRegistration);
        $registration = $saveUser->u2f_registration;
        $counter = $registration->counter;
        $this->assertNotNull($registration);

        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['getSession', 'is', 'getData'])
            ->getMock();
        $this->Trait->setRequest($request);
        $request->expects($this->once())
            ->method('is')
            ->with(
                $this->equalTo('ssl')
            )->will($this->returnValue(true));

        $u2fLib = $this->getMockBuilder(U2F::class)
            ->setConstructorArgs(['https://localhost'])
            ->setMethods(['doAuthenticate'])
            ->getMock();

        $signs = json_decode(json_encode([
            ['fake' => new \stdClass()],
            ['fake2' => new \stdClass()],
        ]));
        $authenticateResponse = json_decode(json_encode([
            'fakeA' => 'fakevaluea',
            'fakeB' => 'fakevalueb',
        ]));

        $this->Trait->getRequest()->expects($this->once())
            ->method('getData')
            ->with($this->equalTo('authenticateResponse'))
            ->will($this->returnValue(json_encode($authenticateResponse)));

        $this->_mockSession([
            'U2f' => [
                'User' => new User([
                    'id' => '00000000-0000-0000-0000-000000000001',
                    'username' => 'user-1',
                ]),
                'authenticateRequest' => json_encode($signs),
            ],
        ]);

        $u2fLib->expects($this->once())
            ->method('doAuthenticate')
            ->with(
                $this->equalTo($signs),
                $this->equalTo([$registration]),
                $this->equalTo($authenticateResponse)
            )
            ->will($this->throwException(new \Exception('Invalid')));

        $this->Trait->expects($this->once())
            ->method('createU2fLib')
            ->will($this->returnValue($u2fLib));

        $response = new Response();
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['action' => 'u2fAuthenticate'])
            ->will($this->returnValue($response));

        $actual = $this->Trait->u2fAuthenticateFinish();
        $this->assertSame($response, $actual);
        $actual = $this->Trait->getRequest()->getSession()->read('U2f');
        $this->assertEquals(
            [
                'User' => new User([
                    'id' => '00000000-0000-0000-0000-000000000001',
                    'username' => 'user-1',
                ]),
                ],
            $actual
        );

        $updatedEntityUser = TableRegistry::getTableLocator()
            ->get('CakeDC/Users.Users')
            ->get('00000000-0000-0000-0000-000000000001');

        $updatedEntity = $updatedEntityUser->u2f_registration;
        $this->assertEquals($counter, $updatedEntity->counter);
    }
}
