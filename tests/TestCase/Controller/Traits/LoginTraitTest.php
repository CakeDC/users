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

use CakeDC\Users\Controller\Component\UsersAuthComponent;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Cake\ORM\Entity;
use CakeDC\Users\Middleware\SocialAuthMiddleware;

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
        $request = new ServerRequest();
        $this->Trait = $this->getMockBuilder('CakeDC\Users\Controller\Traits\LoginTrait')
            ->setMethods(['dispatchEvent', 'redirect', 'set'])
            ->getMockForTrait();

        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['setConfig'])
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
        $this->Trait->request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['is'])
            ->getMock();
        $this->Trait->request->expects($this->any())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(true));
        $this->_mockAuthentication([
            'id' => 1
        ]);
        $this->Trait->Flash = $this->getMockBuilder('Cake\Controller\Component\FlashComponent')
            ->setMethods(['error'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->Trait->Flash->expects($this->never())
            ->method('error');

        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with($this->successLoginRedirect);
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
        $this->Trait->request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['is'])
            ->getMock();
        $this->Trait->request->expects($this->once())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(false));
        $this->Trait->Flash = $this->getMockBuilder('Cake\Controller\Component\FlashComponent')
            ->setMethods(['error'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->Trait->Flash->expects($this->never())
            ->method('error');

        $this->Trait->expects($this->never())
            ->method('redirect');

        $this->_mockAuthentication();
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
            ->setMethods(['logout', 'user'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockAuthentication([
            'id' => 1
        ]);
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with($this->logoutRedirect);
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
        $data = [
            'id' => 11111,
            'username' => 'user-1'
        ];
        $this->_mockFlash();
        $this->_mockAuthentication();
        $this->Trait->Flash->expects($this->once())
            ->method('success')
            ->with('Please enter your email');

        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'socialEmail']);

        $this->Trait->failedSocialLogin(SocialAuthMiddleware::AUTH_ERROR_MISSING_EMAIL, $data, true);
    }

    /**
     * test
     *
     * @return void
     */
    public function testFailedSocialUserNotActive()
    {
        $data = [
            'id' => 111111,
            'username' => 'user-1'
        ];
        $this->_mockFlash();
        $this->_mockAuthentication();
        $this->Trait->Flash->expects($this->once())
            ->method('success')
            ->with('Your user has not been validated yet. Please check your inbox for instructions');

        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login']);

        $this->Trait->failedSocialLogin(SocialAuthMiddleware::AUTH_ERROR_USER_NOT_ACTIVE, $data, true);
    }

    /**
     * test
     *
     * @return void
     */
    public function testFailedSocialUserAccountNotActive()
    {
        $data = [
            'id' => 111111,
            'username' => 'user-1'
        ];
        $this->_mockFlash();
        $this->_mockAuthentication();
        $this->Trait->Flash->expects($this->once())
            ->method('success')
            ->with('Your social account has not been validated yet. Please check your inbox for instructions');

        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login']);

        $this->Trait->failedSocialLogin(SocialAuthMiddleware::AUTH_ERROR_ACCOUNT_NOT_ACTIVE, $data, true);
    }

    /**
     * test
     *
     * @return void
     */
    public function testFailedSocialUserAccount()
    {
        $data = [
            'id' => 111111,
            'username' => 'user-1'
        ];
        $this->_mockFlash();
        $this->_mockAuthentication();
        $this->Trait->Flash->expects($this->once())
            ->method('success')
            ->with('Issues trying to log in with your social account');

        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login']);

        $this->Trait->failedSocialLogin(null, $data, true);
    }
}
