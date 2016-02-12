<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (+1 702 425 5085) (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (+1 702 425 5085) (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Controller\Traits;

use Cake\Event\Event;
use Cake\Mailer\Email;
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

    /**
     * SetUp and create Trait
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $traitMockMethods = array_unique(array_merge(['getUsersTable'], $this->traitMockMethods));
        $this->table = TableRegistry::get('CakeDC/Users.Users');
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
            Email::configTransport('test', [
                'className' => 'Debug'
            ]);
            $this->configEmail = Email::config('default');
            Email::config('default', [
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
            Email::config('default', $this->configEmail);
        }
        parent::tearDown();
    }

    /**
     * mock request for GET
     *
     * @return void
     */
    protected function _mockRequestGet()
    {
        $this->Trait->request = $this->getMockBuilder('Cake\Network\Request')
                ->setMethods(['is', 'referer'])
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
        $this->Trait->request = $this->getMockBuilder('Cake\Network\Request')
                ->setMethods(['is', 'data'])
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
    protected function _mockAuthLoggedIn()
    {
        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user', 'identify', 'setUser', 'redirectUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $user = [
            'id' => '00000000-0000-0000-0000-000000000001',
            'password' => '12345',
        ];
        $this->Trait->Auth->expects($this->any())
            ->method('identify')
            ->will($this->returnValue($user));
        $this->Trait->Auth->expects($this->any())
            ->method('user')
            ->with('id')
            ->will($this->returnValue('00000000-0000-0000-0000-000000000001'));
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
     * @return void
     */
    protected function _mockDispatchEvent(Event $event = null)
    {
        if (is_null($event)) {
            $event = new Event('cool-name-here');
        }
        $this->Trait->expects($this->any())
                ->method('dispatchEvent')
                ->will($this->returnValue($event));
    }
}
