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

namespace CakeDC\Users\Test\TestCase\Controller\Traits;

use Authentication\Authenticator\Result;
use Authentication\Controller\Component\AuthenticationComponent;
use Authentication\Identifier\IdentifierCollection;
use Authentication\Identifier\PasswordIdentifier;
use Authentication\Identity;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Mailer\Email;
use Cake\Mailer\TransportFactory;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakeDC\Auth\Authentication\AuthenticationService;
use CakeDC\Users\Model\Entity\User;
use PHPUnit_Framework_MockObject_RuntimeException;

/**
 * Class BaseTraitTest
 *
 * @package CakeDC\Users\Test\TestCase\Controller\Traits
 */
abstract class BaseTraitTest extends TestCase
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
     * @var MockObject
     */
    public $Trait;

    /**
     * @var Table
     */
    public $table;

    /**
     * SetUp and create Trait
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->loadPlugins(['CakeDC/Users' => ['routes' => true]]);
        $traitMockMethods = array_unique(array_merge(['getUsersTable'], $this->traitMockMethods));
        $this->table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        try {
            $this->Trait = $this->getMockBuilder($this->traitClassName)
                    ->setMethods($traitMockMethods)
                    ->getMock();
            $this->Trait->expects($this->any())
                    ->method('getUsersTable')
                    ->will($this->returnValue($this->table));
        } catch (PHPUnit_Framework_MockObject_RuntimeException $ex) {
            debug($ex);
            $this->fail('Unit tests extending BaseTraitTest should declare the trait class name in the $traitClassName variable before calling setUp()');
        }

        if ($this->mockDefaultEmail) {
            TransportFactory::setConfig('test', [
                'className' => 'Debug',
            ]);
            $this->configEmail = Email::getConfig('default');
            Email::drop('default');
            Email::setConfig('default', [
                'transport' => 'test',
                'from' => 'cakedc@example.com',
            ]);
        }
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->table, $this->Trait);
        if ($this->mockDefaultEmail) {
            Email::drop('default');
            TransportFactory::drop('test');
            //Email::setConfig('default', $this->setConfigEmail);
        }
        parent::tearDown();
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

        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
                ->setMethods($methods)
                ->getMock();
        $request->expects($this->any())
                ->method('is')
                ->with('post')
                ->will($this->returnValue(false));
        $this->Trait->setRequest($request);
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
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
                ->setMethods(['is', 'getData', 'allow'])
                ->getMock();
        $request->expects($this->any())
                ->method('is')
                ->with($with)
                ->will($this->returnValue(true));
        $this->Trait->setRequest($request);
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
     * @param \Authentication\Identifier\IdentifierCollection $identifiers custom identifiers collection
     * @return void
     */
    protected function _mockAuthentication($user = null, $failures = [], $identifiers = null)
    {
        if ($identifiers === null) {
            $passwordIdentifier = $this->getMockBuilder(PasswordIdentifier::class)
                ->setMethods(['needsPasswordRehash'])
                ->getMock();
            $passwordIdentifier->expects($this->any())
                ->method('needsPasswordRehash')
                ->willReturn(false);
            $identifiers = new IdentifierCollection([]);
            $identifiers->set('Password', $passwordIdentifier);
        }

        $config = [
            'identifiers' => [
                'Authentication.Password',
            ],
            'authenticators' => [
                'Authentication.Session',
                'Authentication.Form',
            ],
        ];
        $authentication = $this->getMockBuilder(AuthenticationService::class)->setConstructorArgs([$config])->setMethods([
            'getResult',
            'getFailures',
            'identifiers',
        ])->getMock();

        if ($user) {
            $user = new User($user);
            $identity = new Identity($user);
            $result = new Result($user, Result::SUCCESS);
            $this->Trait->setRequest($this->Trait->getRequest()->withAttribute('identity', $identity));
        } else {
            $result = new Result($user, Result::FAILURE_CREDENTIALS_MISSING);
        }

        $authentication->expects($this->any())
            ->method('getResult')
            ->will($this->returnValue($result));

        $authentication->expects($this->any())
            ->method('getFailures')
            ->will($this->returnValue($failures));

        $authentication->expects($this->any())
            ->method('identifiers')
            ->will($this->returnValue($identifiers));

        $this->Trait->setRequest($this->Trait->getRequest()->withAttribute('authentication', $authentication));

        $controller = new Controller($this->Trait->getRequest());
        $registry = new ComponentRegistry($controller);
        $this->Trait->Authentication = new AuthenticationComponent($registry, [
            'loginRedirect' => $this->successLoginRedirect,
            'logoutRedirect' => $this->logoutRedirect,
            'loginAction' => $this->loginAction,
        ]);
    }

    /**
     * Mock the Authentication service with a Password Rehash being required.
     *
     * @param array $user
     * @param array $failures
     * @return void
     */
    protected function _mockAuthenticationWithPasswordRehash($user = null, $failures = [])
    {
        $config = [
            'identifiers' => [
                'Authentication.Password',
            ],
            'authenticators' => [
                'Authentication.Session',
                'Authentication.Form',
            ],
        ];
        $authentication = $this->getMockBuilder(AuthenticationService::class)->setConstructorArgs([$config])->setMethods([
            'getResult',
            'getFailures',
            'identifiers',
        ])->getMock();
        $authentication->expects($this->any())
            ->method('identifiers')
            ->willReturn($identifiers);
        if ($user) {
            $user = is_object($user) ? $user : new User($user);
            $identity = new Identity($user);
            $result = new Result($user, Result::SUCCESS);
            $this->Trait->setRequest($this->Trait->getRequest()->withAttribute('identity', $identity));
        } else {
            $result = new Result($user, Result::FAILURE_CREDENTIALS_MISSING);
        }

        $authentication->expects($this->any())
            ->method('getResult')
            ->will($this->returnValue($result));

        $authentication->expects($this->any())
            ->method('getFailures')
            ->will($this->returnValue($failures));

        $this->Trait->setRequest($this->Trait->getRequest()->withAttribute('authentication', $authentication));

        //$controller = new Controller($this->Trait->getRequest());
        $registry = new ComponentRegistry($this->Trait);
        $this->Trait->Authentication = new AuthenticationComponent($registry, [
            'loginRedirect' => $this->successLoginRedirect,
            'logoutRedirect' => $this->logoutRedirect,
            'loginAction' => $this->loginAction,
        ]);
    }

    /**
     * mock utility
     *
     * @param Event $event event
     * @param array $result array of data
     * @return void
     */
    protected function _mockDispatchEvent(?Event $event = null, $result = [])
    {
        if (is_null($event)) {
            $event = new Event('cool-name-here');
        }

        if (!empty($result)) {
            $event->setResult(new Entity($result));
        }
        $this->Trait->expects($this->any())
                ->method('dispatchEvent')
                ->will($this->returnValue($event));
    }
}
