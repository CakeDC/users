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

use Authentication\Authenticator\Result;
use Authentication\Controller\Component\AuthenticationComponent;
use Authentication\Identity;
use CakeDC\Users\Authentication\AuthenticationService;
use CakeDC\Users\Model\Entity\User;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Mailer\Email;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use PHPUnit_Framework_MockObject_RuntimeException;

abstract class BaseTraitTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.users',
    ];

    /**
     * Classname of the trait we are about to test
     *
     * @var string
     */
    public $traitClassName = '';
    public $traitMockMethods = [];
    public $mockDefaultEmail = false;

    public $successLoginRedirect = '/home';

    public $logoutRedirect = '/login?fromlogout=1';

    public $loginAction = '/login-page';

    /**
     * SetUp and create Trait
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $traitMockMethods = array_unique(array_merge(['getUsersTable'], $this->traitMockMethods));
        $this->table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        try {
            $this->Trait = $this->getMockBuilder($this->traitClassName)
                    ->setMethods($traitMockMethods)
                    ->getMockForTrait();
            $this->Trait->expects($this->any())
                    ->method('getUsersTable')
                    ->will($this->returnValue($this->table));
        } catch (PHPUnit_Framework_MockObject_RuntimeException $ex) {
            debug($ex);
            $this->fail("Unit tests extending BaseTraitTest should declare the trait class name in the \$traitClassName variable before calling setUp()");
        }

        if ($this->mockDefaultEmail) {
            Email::setConfigTransport('test', [
                'className' => 'Debug'
            ]);
            $this->configEmail = Email::getConfig('default');
            Email::drop('default');
            Email::setConfig('default', [
                'transport' => 'test',
                'from' => 'cakedc@example.com'
            ]);
        }
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->table, $this->Trait);
        if ($this->mockDefaultEmail) {
            Email::drop('default');
            Email::dropTransport('test');
            //Email::setConfig('default', $this->setConfigEmail);
        }
        parent::tearDown();
    }

    /**
     * Mock session and mock session attributes
     *
     * @return void
     */
    protected function _mockSession($attributes)
    {
        $session = new \Cake\Http\Session();

        foreach ($attributes as $field => $value) {
            $session->write($field, $value);
        }

        $this->Trait->request
            ->expects($this->any())
            ->method('getSession')
            ->willReturn($session);
    }

    /**
     * mock request for GET
     *
     * @return void
     */
    protected function _mockRequestGet($withSession = false)
    {
        $methods = ['is', 'referer', 'getData'];

        if ($withSession) {
            $methods[] = 'getSession';
        }

        $this->Trait->request = $this->getMockBuilder('Cake\Http\ServerRequest')
                ->setMethods($methods)
                ->getMock();
        $this->Trait->request->expects($this->any())
                ->method('is')
                ->with('post')
                ->will($this->returnValue(false));
    }

    /**
     * mock Flash Component
     *
     * @return void
     */
    protected function _mockFlash()
    {
        $this->Trait->Flash = $this->getMockBuilder('Cake\Controller\Component\FlashComponent')
                ->setMethods(['error', 'success'])
                ->disableOriginalConstructor()
                ->getMock();
    }

    /**
     * mock Request for POST, is and allow methods
     *
     * @param mixed $with used in with
     * @return void
     */
    protected function _mockRequestPost($with = 'post')
    {
        $this->Trait->request = $this->getMockBuilder('Cake\Http\ServerRequest')
                ->setMethods(['is', 'getData', 'allow'])
                ->getMock();
        $this->Trait->request->expects($this->any())
                ->method('is')
                ->with($with)
                ->will($this->returnValue(true));
    }

    /**
     * Mock Auth and retur user id 1
     *
     * @return void
     */
    protected function _mockAuthLoggedIn($user = [])
    {
        $user += [
            'id' => '00000000-0000-0000-0000-000000000001',
            'password' => '12345',
        ];

        $this->_mockAuthentication($user);
    }

    /**
     * Mock the Authentication service
     *
     * @param array $user
     * @param array $failures
     * @return void
     */
    protected function _mockAuthentication($user = null, $failures = [])
    {
        $config = [
            'identifiers' => [
                'Authentication.Password'
            ],
            'authenticators' => [
                'Authentication.Session',
                'Authentication.Form'
            ]
        ];
        $authentication = $this->getMockBuilder(AuthenticationService::class)->setConstructorArgs([$config])->setMethods([
            'getResult',
            'getFailures'
        ])->getMock();

        if ($user) {
            $user = new User($user);
            $identity = new Identity($user);
            $result = new Result($user, Result::SUCCESS);
            $this->Trait->request = $this->Trait->request->withAttribute('identity', $identity);
        } else {
            $result = new Result($user, Result::FAILURE_CREDENTIALS_MISSING);
        }

        $authentication->expects($this->any())
            ->method('getResult')
            ->will($this->returnValue($result));

        $authentication->expects($this->any())
            ->method('getFailures')
            ->will($this->returnValue($failures));

        $this->Trait->request = $this->Trait->request->withAttribute('authentication', $authentication);

        $controller = new Controller($this->Trait->request);
        $registry = new ComponentRegistry($controller);
        $this->Trait->Authentication = new AuthenticationComponent($registry, [
            'loginRedirect' => $this->successLoginRedirect,
            'logoutRedirect' => $this->logoutRedirect,
            'loginAction' => $this->loginAction
        ]);
    }

    /**
     * mock utility
     *
     * @param Event $event event
     * @param array $result array of data
     * @return void
     */
    protected function _mockDispatchEvent(Event $event = null, $result = [])
    {
        if (is_null($event)) {
            $event = new Event('cool-name-here');
        }

        if (!empty($result)) {
            $event->result = new Entity($result);
        }
        $this->Trait->expects($this->any())
                ->method('dispatchEvent')
                ->will($this->returnValue($event));
    }
}
