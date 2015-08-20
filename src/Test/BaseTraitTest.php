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

namespace Users\Test;

use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use PHPUnit_Framework_MockObject_RuntimeException;

abstract class BaseTraitTest extends TestCase
{
    /**
     * Classname of the trait we are about to test
     *
     * @var string
     */
    public $traitClassName = '';
    public $traitMockMethods = [];

    /**
     * SetUp and create Trait
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $traitMockMethods = array_unique(array_merge(['getUsersTable'], $this->traitMockMethods));
        $this->table = TableRegistry::get('Users.Users');
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
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->table, $this->Trait);
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
     * mock Request for POST
     *
     * @return void
     */
    protected function _mockRequestPost()
    {
        $this->Trait->request = $this->getMockBuilder('Cake\Network\Request')
                ->setMethods(['is', 'data'])
                ->getMock();
        $this->Trait->request->expects($this->once())
                ->method('is')
                ->with('post')
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
            'id' => 1,
            'password' => '12345',
        ];
        $this->Trait->Auth->expects($this->any())
            ->method('identify')
            ->will($this->returnValue($user));
        $this->Trait->Auth->expects($this->any())
            ->method('user')
            ->with('id')
            ->will($this->returnValue(1));
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
