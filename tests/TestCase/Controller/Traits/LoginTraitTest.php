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

use CakeDC\Users\Controller\Component\UsersAuthComponent;
use CakeDC\Users\Controller\Traits\LoginTrait;
use CakeDC\Users\Exception\AccountNotActiveException;
use CakeDC\Users\Exception\MissingEmailException;
use CakeDC\Users\Exception\UserNotActiveException;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Network\Request;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;

class LoginTraitTest extends BaseTraitTest
{
    /**
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->traitClassName = 'CakeDC\Users\Controller\Traits\LoginTrait';
        $this->traitMockMethods = ['dispatchEvent', 'isStopped', 'redirect', 'getUsersTable', 'set'];

        parent::setUp();
        $request = new Request();
        $this->Trait = $this->getMockBuilder('CakeDC\Users\Controller\Traits\LoginTrait')
            ->setMethods(['dispatchEvent', 'redirect'])
            ->getMockForTrait();

        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['config'])
            ->disableOriginalConstructor()
            ->getMock();

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
        $this->Trait->request->expects($this->any())
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
    public function testAfterIdentifyEmptyUser()
    {
        $this->_mockDispatchEvent(new Event('event'));
        $this->Trait->request = $this->getMockBuilder('Cake\Network\Request')
            ->setMethods(['is'])
            ->getMock();
        $this->Trait->request->expects($this->any())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(true));
        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user', 'identify', 'setUser', 'redirectUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $user = [];
        $this->Trait->Auth->expects($this->once())
            ->method('identify')
            ->will($this->returnValue($user));
        $this->Trait->Flash = $this->getMockBuilder('Cake\Controller\Component\FlashComponent')
            ->setMethods(['error'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('Username or password is incorrect', 'default', [], 'auth');
        $this->Trait->login();
    }

    /**
     * test
     *
     * @return void
     */
    public function testAfterIdentifyEmptyUserSocialLogin()
    {
        $this->Trait = $this->getMockBuilder('CakeDC\Users\Controller\Traits\LoginTrait')
            ->setMethods(['dispatchEvent', 'redirect', '_isSocialLogin'])
            ->getMockForTrait();
        $this->Trait->expects($this->any())
            ->method('_isSocialLogin')
            ->will($this->returnValue(true));
        $this->_mockDispatchEvent(new Event('event'));
        $this->Trait->request = $this->getMockBuilder('Cake\Network\Request')
            ->setMethods(['is'])
            ->getMock();
        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user', 'identify', 'setUser', 'redirectUrl'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->Trait->login();
    }

    /**
     * test
     *
     * @return void
     */
    public function testLoginBeforeLoginReturningArray()
    {
        $user = [
            'id' => 1
        ];
        $event = new Event('event');
        $event->result = $user;
        $this->Trait->expects($this->at(0))
            ->method('dispatchEvent')
            ->with(UsersAuthComponent::EVENT_BEFORE_LOGIN)
            ->will($this->returnValue($event));
        $this->Trait->expects($this->at(1))
            ->method('dispatchEvent')
            ->with(UsersAuthComponent::EVENT_AFTER_LOGIN)
            ->will($this->returnValue(new Event('name')));
        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['setUser', 'redirectUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $redirectLoginOK = '/';
        $this->Trait->Auth->expects($this->once())
            ->method('setUser')
            ->with($user);
        $this->Trait->Auth->expects($this->once())
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
    public function testLoginBeforeLoginReturningStoppedEvent()
    {
        $event = new Event('event');
        $event->result = '/';
        $event->stopPropagation();
        $this->Trait->expects($this->at(0))
            ->method('dispatchEvent')
            ->with(UsersAuthComponent::EVENT_BEFORE_LOGIN)
            ->will($this->returnValue($event));
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with('/');
        $this->Trait->login();
    }

    /**
     * test
     *
     * @return void
     */
    public function testLoginGet()
    {
        $this->_mockDispatchEvent(new Event('event'));
        $socialLogin = Configure::read('Users.Social.login');
        Configure::write('Users.Social.login', false);
        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->Trait->request = $this->getMockBuilder('Cake\Network\Request')
            ->setMethods(['is'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->Trait->request->expects($this->at(0))
            ->method('is')
            ->with('post')
            ->will($this->returnValue(false));
        $this->Trait->request->expects($this->at(1))
            ->method('is')
            ->with('post')
            ->will($this->returnValue(false));
        $this->Trait->login();
        Configure::write('Users.Social.login', $socialLogin);
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

    /**
     * test
     *
     * @return void
     */
    public function testFailedSocialLoginMissingEmail()
    {
        $event = new Entity();
        $event->data = [
            'exception' => new MissingEmailException('Email not present'),
            'rawData' => [
                'id' => 11111,
                'username' => 'user-1'
            ]
        ];
        $this->_mockFlash();
        $this->_mockRequestGet();
        $this->Trait->Flash->expects($this->once())
            ->method('success')
            ->with('Please enter your email');

        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'socialEmail']);

        $this->Trait->failedSocialLogin($event->data['exception'], $event->data['rawData'], true);
    }

    /**
     * test
     *
     * @return void
     */
    public function testFailedSocialUserNotActive()
    {
        $event = new Entity();
        $event->data = [
            'exception' => new UserNotActiveException('Facebook user-1'),
            'rawData' => [
                'id' => 111111,
                'username' => 'user-1'
            ]
        ];
        $this->_mockFlash();
        $this->_mockRequestGet();
        $this->Trait->Flash->expects($this->once())
            ->method('success')
            ->with('Your user has not been validated yet. Please check your inbox for instructions');

        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login']);

        $this->Trait->Auth->expects($this->at(0))
            ->method('config')
            ->with('authError', 'Your user has not been validated yet. Please check your inbox for instructions');

        $this->Trait->Auth->expects($this->at(1))
            ->method('config')
            ->with('flash.params', ['class' => 'success']);

        $this->Trait->failedSocialLogin($event->data['exception'], $event->data['rawData'], true);
    }

    /**
     * test
     *
     * @return void
     */
    public function testFailedSocialUserAccountNotActive()
    {
        $event = new Entity();
        $event->data = [
            'exception' => new AccountNotActiveException('Facebook user-1'),
            'rawData' => [
                'id' => 111111,
                'username' => 'user-1'
            ]
        ];
        $this->_mockFlash();
        $this->_mockRequestGet();
        $this->Trait->Flash->expects($this->once())
            ->method('success')
            ->with('Your social account has not been validated yet. Please check your inbox for instructions');

        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login']);

        $this->Trait->failedSocialLogin($event->data['exception'], $event->data['rawData'], true);
    }


    /**
     * test
     *
     * @return void
     */
    public function testFailedSocialUserAccount()
    {
        $event = new Entity();
        $event->data = [
            'rawData' => [
                'id' => 111111,
                'username' => 'user-1'
            ]
        ];
        $this->_mockFlash();
        $this->_mockRequestGet();
        $this->Trait->Flash->expects($this->once())
            ->method('success')
            ->with('Issues trying to log in with your social account');

        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login']);

        $this->Trait->failedSocialLogin(null, $event->data['rawData'], true);
    }
}
