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
        $event = new Entity();
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
        $event = new Entity();
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

        $this->Trait->failedSocialLogin(null, $event->data['rawData'], true);
    }

    /**
     * testVerifyHappy
     *
     */
    public function testVerifyHappy()
    {
        Configure::write('Users.GoogleAuthenticator.login', true);

        $this->Trait->request = $this->getMockBuilder('Cake\Network\Request')
            ->setMethods(['is', 'getData', 'allow', 'getSession'])
            ->getMock();
        $this->Trait->request->expects($this->once())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(false));

        $this->_mockSession([
            'temporarySession' => [
                'id' => 1,
                'secret_verified' => 1,
            ]
        ]);
        $this->Trait->verify();
    }

    /**
     * testVerifyNoUser
     *
     */
    public function testVerifyNoUser()
    {
        Configure::write('Users.GoogleAuthenticator.login', true);

        $this->Trait->request = $this->getMockBuilder('Cake\Network\Request')
            ->setMethods(['is', 'getData', 'allow', 'getSession'])
            ->getMock();
        $this->Trait->request->expects($this->never())
            ->method('is')
            ->with('post');
        $this->_mockSession([]);
        $this->_mockFlash();
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('Invalid request.');
        $this->Trait->verify();
    }

    /**
     * testVerifyHappy
     *
     */
    public function testVerifyNotEnabled()
    {
        $this->_mockFlash();
        Configure::write('Users.GoogleAuthenticator.login', false);
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('Please enable Google Authenticator first.');
        $this->Trait->verify();
    }

    /**
     * testVerifyHappy
     *
     */
    public function testVerifyGetShowQR()
    {
        Configure::write('Users.GoogleAuthenticator.login', true);

        $this->Trait->GoogleAuthenticator = $this->getMockBuilder(GoogleAuthenticatorComponent::class)
            ->disableOriginalConstructor()
            ->setMethods(['createSecret', 'getQRCodeImageAsDataUri'])
            ->getMock();

        $this->Trait->request = $this->getMockBuilder(ServerRequest::class)
            ->setMethods(['is', 'getData', 'allow', 'getSession'])
            ->getMock();
        $this->Trait->request->expects($this->once())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(false));
        $this->_mockSession([
            'temporarySession' => [
                'id' => '00000000-0000-0000-0000-000000000001',
                'email' => 'email@example.com',
                'secret_verified' => 0,
            ]
        ]);
        $this->Trait->GoogleAuthenticator->expects($this->at(0))
            ->method('createSecret')
            ->will($this->returnValue('newSecret'));
        $this->Trait->GoogleAuthenticator->expects($this->at(1))
            ->method('getQRCodeImageAsDataUri')
            ->with('email@example.com', 'newSecret')
            ->will($this->returnValue('newDataUriGenerated'));
        $this->Trait->expects($this->at(0))
            ->method('set')
            ->with(['secretDataUri' => 'newDataUriGenerated']);
        $this->Trait->verify();
    }
}
