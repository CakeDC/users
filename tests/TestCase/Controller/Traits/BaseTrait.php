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

use Authentication\Authenticator\Result;
use Authentication\Controller\Component\AuthenticationComponent;
use Authentication\Identity;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Cake\Http\Session;
use Cake\Mailer\Mailer;
use Cake\Mailer\TransportFactory;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakeDC\Auth\Authentication\AuthenticationService;
use CakeDC\Users\Model\Entity\User;
use JMac\Testing\Double;
use JMac\Testing\Integrations\PHPUnit\VerifiesDoubles;
use PHPUnit\Framework\MockObject\RuntimeException;

/**
 * Class BaseTraitTest
 *
 * @package CakeDC\Users\Test\TestCase\Controller\Traits
 */
class BaseTrait extends TestCase
{
    use VerifiesDoubles;

    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = [
        'plugin.CakeDC/Users.Users',
    ];

    /**
     * Classname of the trait we are about to test
     *
     * @var string
     */
    public $traitClassName = '';
    public $traitMockMethods = [];
    public $traitMockAddMethods = [];
    public $mockDefaultEmail = false;
    public $skipUsersMock = false;

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
     * @var array|null
     */
    public ?array $configEmail;

    /**
     * SetUp and create Trait
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->loadPlugins(['CakeDC/Users' => ['routes' => true]]);
        $traitMockMethods = array_unique(array_merge($this->traitMockMethods, ['getUsersTable']));
        $this->table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        try {
            $buildTrait = $this->getMockBuilder($this->traitClassName)
                ->onlyMethods($traitMockMethods);
            if (class_exists($this->traitClassName)) {
                $buildTrait->setConstructorArgs([new ServerRequest()]);
            }
            $this->Trait = $buildTrait->getMock();

            if (!$this->skipUsersMock) {
                $this->Trait->method('getUsersTable')
                    ->willReturn($this->table);
            }
        } catch (RuntimeException $ex) {
            $this->fail('Unit tests extending BaseTraitTest should declare the trait class name in the $traitClassName variable before calling setUp()');
        }

        if ($this->mockDefaultEmail) {
            TransportFactory::setConfig('test', [
                'className' => 'Debug',
            ]);
            $this->configEmail = Mailer::getConfig('default');
            Mailer::drop('default');
            Mailer::setConfig('default', [
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
            Mailer::drop('default');
            TransportFactory::drop('test');
        }
        parent::tearDown();
    }

    /**
     * Mock session and mock session attributes
     *
     * @return \Cake\Http\Session
     */
    protected function mockSession(array $attributes): Session
    {
        $session = new Session();

        foreach ($attributes as $field => $value) {
            $session->write($field, $value);
        }

        $this->Trait
            ->getRequest()
            ->expects($this->atLeastOnce())
            ->method('getSession')
            ->willReturn($session);

        return $session;
    }

    /**
     * mock request for GET
     *
     * @return void
     */
    protected function mockRequestGet(bool $withSession = false): void
    {
        $methods = ['is', 'referer', 'getData'];

        if ($withSession) {
            $methods[] = 'getSession';
        }

        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
                ->onlyMethods($methods)
                ->getMock();
        $request->method('is')
                ->willReturn(false);
        $this->Trait->setRequest($request);
    }

    /**
     * mock Flash Component
     *
     * @return void
     */
    protected function mockFlash(): object
    {
        $flash = Double::for('Cake\Controller\Component\FlashComponent');
        $flash->allows('set');
        $this->Trait->components()->set('Flash', $flash);

        return $flash;
    }

    /**
     * mock Request for POST, is and allow methods
     *
     * @param mixed $with used in with
     * @return void
     */
    protected function mockRequestPost(mixed $with = 'post'): void
    {
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
                ->onlyMethods(['is', 'getData'])
                ->getMock();
        $request->method('is')
                ->willReturn(true);
        $this->Trait->setRequest($request);
    }

    /**
     * Mock Auth and retur user id 1
     *
     * @return void
     */
    protected function mockAuthLoggedIn(array $user = []): void
    {
        $user += [
            'id' => '00000000-0000-0000-0000-000000000001',
            'password' => '12345',
        ];

        $this->mockAuthentication($user, [], false);
    }

    /**
     * Mock the Authentication service
     *
     * @param array|null $user user data to authenticate, null for anonymous
     * @param array $failures list of authentication failures
     * @param bool $expectGetResult whether the service result is consumed (login flows) or not
     * @return void
     */
    protected function mockAuthentication(?array $user = null, array $failures = [], bool $expectGetResult = true): void
    {
        $config = [
            'authenticators' => [
                'Authentication.Session',
                'Authentication.Form' => [
                    'identifier' => [
                        'className' => 'Authentication.Password',
                    ],
                ],
            ],
        ];
        $authentication = $this->getMockBuilder(AuthenticationService::class)->setConstructorArgs([$config])->onlyMethods([
            'getResult',
            'getFailures',
        ])->getMock();

        if ($user) {
            $user = new User($user);
            $identity = new Identity($user);
            $result = new Result($user, Result::SUCCESS);
            $this->Trait->setRequest($this->Trait->getRequest()->withAttribute('identity', $identity));
        } else {
            $result = new Result($user, Result::FAILURE_CREDENTIALS_MISSING);
        }

        if ($expectGetResult) {
            $authentication->expects($this->atLeastOnce())
                ->method('getResult')
                ->willReturn($result);
        } else {
            $authentication->expects($this->never())
                ->method('getResult');
        }

        $authentication->method('getFailures')
            ->willReturn($failures);

        $this->Trait->setRequest($this->Trait->getRequest()->withAttribute('authentication', $authentication));

        $controller = new Controller($this->Trait->getRequest());
        $registry = new ComponentRegistry($controller);

        $authentication = new AuthenticationComponent($registry, [
            'loginRedirect' => $this->successLoginRedirect,
            'logoutRedirect' => $this->logoutRedirect,
            'loginAction' => $this->loginAction,
        ]);

        $this->Trait->components()->set('Authentication', $authentication);
    }

    /**
     * Mock the Authentication service with a Password Rehash being required.
     *
     * @param array $user
     * @param array $failures
     * @return void
     */
    protected function mockAuthenticationWithPasswordRehash(?array $user = null, array $failures = []): void
    {
        $config = [
            'authenticators' => [
                'Authentication.Session',
                'Authentication.Form' => [
                    'identifier' => [
                        'className' => 'Authentication.Password',
                    ],
                ],
            ],
        ];
        $authentication = $this->getMockBuilder(AuthenticationService::class)->setConstructorArgs([$config])->onlyMethods([
            'getResult',
            'getFailures',
        ])->getMock();
        if ($user) {
            $user = is_object($user) ? $user : new User($user);
            $identity = new Identity($user);
            $result = new Result($user, Result::SUCCESS);
            $this->Trait->setRequest($this->Trait->getRequest()->withAttribute('identity', $identity));
        } else {
            $result = new Result($user, Result::FAILURE_CREDENTIALS_MISSING);
        }

        $authentication->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn($result);

        $authentication->method('getFailures')
            ->willReturn($failures);

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
    protected function mockDispatchEvent(?Event $event = null, array $result = []): void
    {
        if (is_null($event)) {
            $event = new Event('cool-name-here');
        }

        if (!empty($result)) {
            $event->setResult(new Entity($result));
        }

        $this->Trait->expects($this->atLeastOnce())
                ->method('dispatchEvent')
                ->willReturn($event);
    }
}
