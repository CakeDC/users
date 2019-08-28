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

use Cake\Controller\Component\FlashComponent;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Cake\Mailer\Email;
use Cake\Mailer\TransportFactory;
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
            TransportFactory::setConfig('test', ['className' => 'Debug']);
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
            TransportFactory::drop('test');
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
        $this->Trait->request
            ->expects($this->any())
            ->method('session')
            ->willReturn($session);
    }

    /**
     * mock request for GET
     *
     * @return void
     */
    protected function _mockRequestGet($expectation = [], $withSession = false)
    {
        $methods = ['is', 'referer', 'getData'];

        if ($withSession) {
            $methods[] = 'session';
            $methods[] = 'getSession';
        }

        $this->Trait->request = $this->getMockBuilder(ServerRequest::class)
                ->setMethods($methods)
                ->getMock();

        if (empty($expectation)) {
            $expectation = [
                'method' => 'is',
                'with' => 'post',
                'returnValue' => false
            ];
        }

        $this->Trait->request->expects($this->any())
            ->method($expectation['method'])
            ->with($expectation['with'])
            ->will($this->returnValue($expectation['returnValue']));
    }

    /**
     * mock Flash Component
     *
     * @return void
     */
    protected function _mockFlash()
    {
        $this->Trait->Flash = $this->getMockBuilder(FlashComponent::class)
                ->setMethods(['error', 'success'])
                ->disableOriginalConstructor()
                ->getMock();
    }

    protected function _mockRequest()
    {
        $this->Trait->request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['is', 'getData', 'allow'])
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
        $this->_mockRequest();
        $this->Trait->request->expects($this->any())
                ->method('is')
                ->with($with)
                ->will($this->returnValue(true));
    }

    /**
     * mock Request for POST, is and allow methods
     *
     * @param mixed $with used in with
     * @return void
     */
    protected function _mockRequestPostIsAjax($with = 'post', $isAjax = false)
    {
        $this->Trait->request = $this->getMockBuilder('Cake\Http\ServerRequest')
                ->setMethods(['is', 'getData', 'allow'])
                ->getMock();
        $this->Trait->request->expects($this->at(0))
                ->method('is')
                ->with('ajax')
                ->will($this->returnValue($isAjax));
        $this->Trait->request->expects($this->at(1))
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
        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user', 'identify', 'setUser', 'redirectUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $user += [
            'id' => '00000000-0000-0000-0000-000000000001',
            'password' => '12345',
        ];
        $this->Trait->Auth->expects($this->any())
            ->method('identify')
            ->will($this->returnValue($user));
        $this->Trait->Auth->expects($this->any())
            ->method('user')
            ->with('id')
            ->will($this->returnValue($user['id']));
    }

    /**
     * Mock the Auth component
     *
     * @return void
     */
    protected function _mockAuth()
    {
        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user', 'identify', 'setUser', 'redirectUrl'])
            ->disableOriginalConstructor()
            ->getMock();
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
