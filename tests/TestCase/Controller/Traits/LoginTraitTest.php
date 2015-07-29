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

namespace Users\Test\TestCase\Controller\Traits;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Network\Request;
use Cake\TestSuite\TestCase;
use Opauth\Opauth\Response;
use Users\Controller\Traits\LoginTrait;

class LoginTraitTest extends TestCase
{
    /**
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $request = new Request();
        $this->Trait = $this->getMockBuilder('Users\Controller\Traits\LoginTrait')
            ->setMethods(['dispatchEvent', 'isStopped', 'redirect'])
            ->getMockForTrait();
        $this->Trait->request = $request;
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * mock utility
     *
     * @return void
     */
    protected function _mockDispatchEvent(Event $event)
    {
        $this->Trait->expects($this->any())
                ->method('dispatchEvent')
                ->will($this->returnValue($event));
    }

    /**
     * test
     *
     * @return void
     */
    public function testLoginHappy()
    {
        $this->_mockDispatchEvent(new Event('event'));
        $this->Trait->request = $this->getMockBuilder('Cake\Network\Request')
                ->setMethods(['is'])
                ->getMock();
        $this->Trait->request->expects($this->once())
                ->method('is')
                ->with('post')
                ->will($this->returnValue(true));
        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user', 'identify', 'setUser', 'redirectUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $user = [
            'id' => 1,
        ];
        $redirectLoginOK = '/';
        $this->Trait->Auth->expects($this->at(0))
            ->method('identify')
            ->will($this->returnValue($user));
        $this->Trait->Auth->expects($this->at(1))
                ->method('setUser')
                ->with($user);
        $this->Trait->Auth->expects($this->at(2))
            ->method('redirectUrl')
            ->will($this->returnValue($redirectLoginOK));
        $this->Trait->expects($this->once())
                ->method('redirect')
                ->with($redirectLoginOK);
        $this->Trait->login();
    }

    /**
     * test
     *
     * @return void
     */
    public function testLogout()
    {
        $this->_mockDispatchEvent(new Event('event'));
        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['logout'])
            ->disableOriginalConstructor()
            ->getMock();
        $redirectLogoutOK = '/';
        $this->Trait->Auth->expects($this->once())
            ->method('logout')
            ->will($this->returnValue($redirectLogoutOK));
        $this->Trait->expects($this->once())
                ->method('redirect')
                ->with($redirectLogoutOK);
        $this->Trait->Flash = $this->getMockBuilder('Cake\Controller\Component\FlashComponent')
            ->setMethods(['success'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->Trait->Flash->expects($this->once())
                ->method('success')
                ->with('You\'ve successfully logged out');
        $this->Trait->logout();
    }
}
