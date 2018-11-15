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

use CakeDC\Users\Controller\Component\GoogleAuthenticatorComponent;
use CakeDC\Users\Controller\Component\UsersAuthComponent;
use CakeDC\Users\Controller\Traits\LoginTrait;
use CakeDC\Users\Exception\AccountNotActiveException;
use CakeDC\Users\Exception\MissingEmailException;
use CakeDC\Users\Exception\UserNotActiveException;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
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
        $this->Trait->GoogleAuthenticator = $this->getMockBuilder(GoogleAuthenticatorComponent::class)
            ->disableOriginalConstructor()
            ->setMethods(['createSecret', 'getQRCodeImageAsDataUri'])
            ->getMock();
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
        $this->Trait->GoogleAuthenticator = $this->getMockBuilder(GoogleAuthenticatorComponent::class)
            ->disableOriginalConstructor()
            ->setMethods(['createSecret', 'getQRCodeImageAsDataUri'])
            ->getMock();
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
            ->setMethods(['logout', 'user'])
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
        $this->Trait->GoogleAuthenticator = $this->getMockBuilder(GoogleAuthenticatorComponent::class)
            ->disableOriginalConstructor()
            ->setMethods(['createSecret', 'getQRCodeImageAsDataUri'])
            ->getMock();
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
        $this->Trait->GoogleAuthenticator = $this->getMockBuilder(GoogleAuthenticatorComponent::class)
            ->disableOriginalConstructor()
            ->setMethods(['createSecret', 'getQRCodeImageAsDataUri'])
            ->getMock();
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

    /**
     * Tests that a GET request causes a a new secret to be generated in case it's
     * not already present in the session.
     */
    public function testVerifyGetGeneratesNewSecret()
    {
        Configure::write('Users.GoogleAuthenticator.login', true);

        $this->Trait->GoogleAuthenticator = $this
            ->getMockBuilder(GoogleAuthenticatorComponent::class)
            ->disableOriginalConstructor()
            ->setMethods(['createSecret', 'getQRCodeImageAsDataUri'])
            ->getMock();

        $this->Trait->request = $this
            ->getMockBuilder(ServerRequest::class)
            ->setMethods(['is', 'getData', 'allow', 'getSession'])
            ->getMock();
        $this->Trait->request
            ->expects($this->once())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(false));

        $this->Trait->GoogleAuthenticator
            ->expects($this->at(0))
            ->method('createSecret')
            ->will($this->returnValue('newSecret'));
        $this->Trait->GoogleAuthenticator
            ->expects($this->at(1))
            ->method('getQRCodeImageAsDataUri')
            ->with('email@example.com', 'newSecret')
            ->will($this->returnValue('newDataUriGenerated'));

        $session = $this->_mockSession([
            'temporarySession' => [
                'id' => '00000000-0000-0000-0000-000000000001',
                'email' => 'email@example.com',
                'secret_verified' => false,
            ]
        ]);
        $this->Trait->verify();

        $this->assertEquals(
            [
                'temporarySession' => [
                    'id' => '00000000-0000-0000-0000-000000000001',
                    'email' => 'email@example.com',
                    'secret_verified' => false,
                    'secret' => 'newSecret'
                ]
            ],
            $session->read()
        );
    }

    /**
     * Tests that a GET request does not cause a new secret to be generated in case
     * it's already present in the session.
     */
    public function testVerifyGetDoesNotGenerateNewSecret()
    {
        Configure::write('Users.GoogleAuthenticator.login', true);

        $this->Trait->GoogleAuthenticator = $this
            ->getMockBuilder(GoogleAuthenticatorComponent::class)
            ->disableOriginalConstructor()
            ->setMethods(['createSecret', 'getQRCodeImageAsDataUri'])
            ->getMock();

        $this->Trait->request = $this
            ->getMockBuilder(ServerRequest::class)
            ->setMethods(['is', 'getData', 'allow', 'getSession'])
            ->getMock();
        $this->Trait->request
            ->expects($this->once())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(false));

        $this->Trait->GoogleAuthenticator
            ->expects($this->never())
            ->method('createSecret');
        $this->Trait->GoogleAuthenticator
            ->expects($this->at(0))
            ->method('getQRCodeImageAsDataUri')
            ->with('email@example.com', 'alreadyPresentSecret')
            ->will($this->returnValue('newDataUriGenerated'));

        $session = $this->_mockSession([
            'temporarySession' => [
                'id' => '00000000-0000-0000-0000-000000000001',
                'email' => 'email@example.com',
                'secret_verified' => false,
                'secret' => 'alreadyPresentSecret'
            ]
        ]);
        $this->Trait->verify();

        $this->assertEquals(
            [
                'temporarySession' => [
                    'id' => '00000000-0000-0000-0000-000000000001',
                    'email' => 'email@example.com',
                    'secret_verified' => false,
                    'secret' => 'alreadyPresentSecret'
                ]
            ],
            $session->read()
        );
    }

    /**
     * Tests that posting a valid code causes verification to succeed.
     */
    public function testVerifyPostValidCode()
    {
        Configure::write('Users.GoogleAuthenticator.login', true);

        $this->_mockDispatchEvent(new Event('event'));
        $this->Trait->GoogleAuthenticator = $this->getMockBuilder(GoogleAuthenticatorComponent::class)
             ->disableOriginalConstructor()
             ->setMethods(['createSecret', 'verifyCode', 'getQRCodeImageAsDataUri'])
             ->getMock();

        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
              ->setMethods(['setUser', 'redirectUrl'])
              ->disableOriginalConstructor()
              ->getMock();

        $this->Trait->request = $this->getMockBuilder(ServerRequest::class)
             ->setMethods(['is', 'getData', 'allow', 'getSession'])
             ->getMock();
        $this->Trait->request->expects($this->once())
             ->method('is')
             ->with('post')
             ->will($this->returnValue(true));
        $this->Trait->request->expects($this->once())
             ->method('getData')
             ->with('code')
             ->will($this->returnValue('123456'));

        $this->Trait->GoogleAuthenticator
            ->expects($this->never())
            ->method('createSecret');
        $this->Trait->GoogleAuthenticator
            ->expects($this->at(0))
            ->method('getQRCodeImageAsDataUri')
            ->with('email@example.com', 'yyy')
            ->will($this->returnValue('newDataUriGenerated'));
        $this->Trait->GoogleAuthenticator
            ->expects($this->at(1))
             ->method('verifyCode')
             ->with('yyy', '123456')
             ->will($this->returnValue(true));

        $this->Trait->Auth
            ->expects($this->at(0))
            ->method('setUser')
            ->with([
                'id' => '00000000-0000-0000-0000-000000000001',
                'email' => 'email@example.com',
                'secret_verified' => true
            ]);
        $this->Trait->Auth
            ->expects($this->at(1))
            ->method('redirectUrl')
            ->will($this->returnValue('/'));

        $this->assertFalse($this->table->exists([
            'id' => '00000000-0000-0000-0000-000000000001',
            'secret_verified' => true
        ]));

        $session = $this->_mockSession([
            'temporarySession' => [
                'id' => '00000000-0000-0000-0000-000000000001',
                'email' => 'email@example.com',
                'secret_verified' => false,
                'secret' => 'yyy'
            ]
        ]);
        $this->Trait->verify();

        $this->assertTrue($this->table->exists([
            'id' => '00000000-0000-0000-0000-000000000001',
            'secret_verified' => true
        ]));

        $this->assertEmpty($session->read());
    }

    /**
     * Tests that posting and invalid code causes verification to fail.
     */
    public function testVerifyPostInvalidCode()
    {
        Configure::write('Users.GoogleAuthenticator.login', true);

        $this->Trait->GoogleAuthenticator = $this
            ->getMockBuilder(GoogleAuthenticatorComponent::class)
            ->disableOriginalConstructor()
            ->setMethods(['createSecret', 'verifyCode', 'getQRCodeImageAsDataUri'])
            ->getMock();

        $this->Trait->Auth = $this
            ->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['setUser'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->Trait->Flash = $this
            ->getMockBuilder('Cake\Controller\Component\FlashComponent')
            ->setMethods(['error'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->Trait->request = $this
            ->getMockBuilder(ServerRequest::class)
            ->setMethods(['is', 'getData', 'allow', 'getSession'])
            ->getMock();
        $this->Trait->request
            ->expects($this->once())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(true));
        $this->Trait->request
            ->expects($this->once())
            ->method('getData')
            ->with('code')
            ->will($this->returnValue('invalid'));

        $this->Trait->GoogleAuthenticator
            ->expects($this->never())
            ->method('createSecret');
        $this->Trait->GoogleAuthenticator
            ->expects($this->at(0))
            ->method('getQRCodeImageAsDataUri')
            ->with('email@example.com', 'yyy')
            ->will($this->returnValue('newDataUriGenerated'));
        $this->Trait->GoogleAuthenticator
            ->expects($this->at(1))
            ->method('verifyCode')
            ->with('yyy', 'invalid')
            ->will($this->returnValue(false));

        $this->Trait->Auth
            ->expects($this->never())
            ->method('setUser');

        $this->Trait->Flash
            ->expects($this->once())
            ->method('error')
            ->with('Verification code is invalid. Try again', 'default', [], 'auth');

        $this->Trait
            ->expects($this->once())
            ->method('redirect')
            ->with([
                'plugin' => 'CakeDC/Users',
                'controller' => 'Users',
                'action' => 'login',
                'prefix' => false,
                '?' => []
            ]);

        $this->assertFalse($this->table->exists([
            'id' => '00000000-0000-0000-0000-000000000001',
            'secret_verified' => true
        ]));

        $session = $this->_mockSession([
            'temporarySession' => [
                'id' => '00000000-0000-0000-0000-000000000001',
                'email' => 'email@example.com',
                'secret_verified' => false,
                'secret' => 'yyy'
            ]
        ]);
        $this->Trait->verify();

        $this->assertFalse($this->table->exists([
            'id' => '00000000-0000-0000-0000-000000000001',
            'secret_verified' => true
        ]));

        $this->assertEmpty($session->read());
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

        $this->Trait->request
            ->expects($this->any())
            ->method('getSession')
            ->willReturn($session);

        return $session;
    }
}
