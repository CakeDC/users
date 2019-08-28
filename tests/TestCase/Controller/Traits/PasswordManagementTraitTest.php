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

use CakeDC\Users\Controller\Traits\PasswordManagementTrait;
use Cake\Auth\PasswordHasherFactory;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * Class PasswordManagementTraitTest
 * @package CakeDC\Users\Test\TestCase\Controller\Traits
 * @property PasswordManagementTrait Trait
 */
class PasswordManagementTraitTest extends BaseTraitTest
{
    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        $this->traitClassName = 'CakeDC\Users\Controller\Traits\PasswordManagementTrait';
        $this->traitMockMethods = ['set', 'redirect', 'validate', 'log', 'dispatchEvent'];
        $this->mockDefaultEmail = true;
        parent::setUp();
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
    public function testChangePasswordHappy()
    {
        $this->assertEquals('12345', $this->table->get('00000000-0000-0000-0000-000000000001')->password);
        $this->_mockRequestPost(['post', 'put']);
        $this->_mockAuthLoggedIn();
        $this->_mockFlash();
        $this->Trait->request->expects($this->once())
                ->method('getData')
                ->will($this->returnValue([
                    'password' => 'new',
                    'password_confirm' => 'new',
                ]));
        $this->Trait->expects($this->once())
                ->method('redirect')
                ->with(['prefix' => false, 'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'profile', 'prefix' => false]);
        $this->Trait->Flash->expects($this->any())
            ->method('success')
            ->with('Password has been changed successfully');
        $this->Trait->changePassword();
        $hasher = PasswordHasherFactory::build('Default');
        $this->assertTrue($hasher->check('new', $this->table->get('00000000-0000-0000-0000-000000000001')->password));
    }

    /**
     * test
     *
     * @return void
     */
    public function testChangePasswordWithError()
    {
        $this->assertEquals('12345', $this->table->get('00000000-0000-0000-0000-000000000001')->password);
        $this->_mockRequestPost(['post', 'put']);
        $this->_mockAuthLoggedIn();
        $this->_mockFlash();
        $this->Trait->request->expects($this->once())
                ->method('getData')
                ->will($this->returnValue([
                    'password' => 'new',
                    'password_confirm' => 'wrong_new',
                ]));
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('Password could not be changed');
        $this->Trait->changePassword();
    }

    /**
     * test
     *
     * @return void
     */
    public function testChangePasswordWithAfterChangeEvent()
    {
        $this->assertEquals('12345', $this->table->get('00000000-0000-0000-0000-000000000001')->password);
        $this->_mockRequestPost(['post', 'put']);
        $this->_mockAuthLoggedIn();
        $this->_mockFlash();
        $this->Trait->request->expects($this->once())
            ->method('getData')
            ->will($this->returnValue([
                'password' => 'new',
                'password_confirm' => 'new',
            ]));
        $event = new Event('event');
        $event->result = [
            'action' => 'newAction',
        ];
        $this->Trait->expects($this->once())
            ->method('dispatchEvent')
            ->will($this->returnValue($event));
        $this->Trait->expects($this->once())
            ->method('redirect')
            ->with(['action' => 'newAction']);
        $this->Trait->Flash->expects($this->any())
            ->method('success')
            ->with('Password has been changed successfully');
        $this->Trait->changePassword();
        $hasher = PasswordHasherFactory::build('Default');
        $this->assertTrue($hasher->check('new', $this->table->get('00000000-0000-0000-0000-000000000001')->password));
    }

    /**
     * test
     *
     * @return void
     */
    public function testChangePasswordWithSamePassword()
    {
        $this->assertEquals(
            '$2y$10$IPPgJNSfvATsMBLbv/2r8OtpyTBibyM1g5GDxD4PivW9qBRwRkRbC',
            $this->table->get('00000000-0000-0000-0000-000000000006')->password
        );
        $this->_mockRequestPost(['post', 'put']);
        $this->_mockAuthLoggedIn(['id' => '00000000-0000-0000-0000-000000000006', 'password' => '$2y$10$IPPgJNSfvATsMBLbv/2r8OtpyTBibyM1g5GDxD4PivW9qBRwRkRbC']);
        $this->_mockFlash();
        $this->Trait->request->expects($this->once())
            ->method('getData')
            ->will($this->returnValue([
                'current_password' => '12345',
                'password' => '12345',
                'password_confirm' => '12345',
            ]));
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('You cannot use the current password as the new one');
        $this->Trait->changePassword();
    }

    /**
     * test
     *
     * @return void
     */
    public function testChangePasswordWithEmptyCurrentPassword()
    {
        $this->_mockRequestPost(['post', 'put']);
        $this->_mockAuthLoggedIn(['id' => '00000000-0000-0000-0000-000000000006', 'password' => '$2y$10$IPPgJNSfvATsMBLbv/2r8OtpyTBibyM1g5GDxD4PivW9qBRwRkRbC']);
        $this->_mockFlash();
        $this->Trait->request->expects($this->once())
            ->method('getData')
            ->will($this->returnValue([
                'current_password' => '',
                'password' => '54321',
                'password_confirm' => '54321',
            ]));
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('Password could not be changed');
        $this->Trait->changePassword();
    }

    /**
     * test
     *
     * @return void
     */
    public function testChangePasswordWithWrongCurrentPassword()
    {
        $this->assertEquals(
            '$2y$10$IPPgJNSfvATsMBLbv/2r8OtpyTBibyM1g5GDxD4PivW9qBRwRkRbC',
            $this->table->get('00000000-0000-0000-0000-000000000006')->password
        );
        $this->_mockRequestPost(['post', 'put']);
        $this->_mockAuthLoggedIn(['id' => '00000000-0000-0000-0000-000000000006', 'password' => '$2y$10$IPPgJNSfvATsMBLbv/2r8OtpyTBibyM1g5GDxD4PivW9qBRwRkRbC']);
        $this->_mockFlash();
        $this->Trait->request->expects($this->once())
            ->method('getData')
            ->will($this->returnValue([
                'current_password' => 'wrong-password',
                'password' => '12345',
                'password_confirm' => '12345',
            ]));
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('The current password does not match');
        $this->Trait->changePassword();
    }

    /**
     * test
     *
     * @return void
     */
    public function testChangePasswordWithInvalidUser()
    {
        $this->_mockRequestPost(['post', 'put']);
        $this->_mockAuthLoggedIn(['id' => '12312312-0000-0000-0000-000000000002', 'password' => 'invalid-pass']);
        $this->_mockFlash();
        $this->Trait->request->expects($this->once())
                ->method('getData')
                ->will($this->returnValue([
                    'password' => 'new',
                    'password_confirm' => 'new',
                ]));
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('User was not found');
        $this->Trait->changePassword();
    }

    /**
     * test
     *
     * @return void
     */
    public function testChangePasswordGetLoggedIn()
    {
        $this->Trait->request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['is', 'referer', 'getData'])
            ->getMock();
        $this->Trait->request->expects($this->any())
            ->method('is')
            ->with(['post', 'put'])
            ->will($this->returnValue(false));
        $this->_mockAuthLoggedIn();
        $this->Trait->expects($this->any())
                ->method('set')
                ->will($this->returnCallback(function ($param1, $param2 = null) {
                    if ($param1 === 'validatePassword') {
                        TestCase::assertEquals($param2, true);
                    }
                }));
        $this->Trait->changePassword();
    }

    /**
     * test
     *
     * @return void
     */
    public function testChangePasswordGetNotLoggedInInsideResetPasswordFlow()
    {
        $this->_mockRequestGet([
            'method' => 'is',
            'with' => ['post', 'put'],
            'returnValue' => false
        ], true);
        $this->_mockAuth();
        $this->_mockFlash();
        $this->_mockSession([
            Configure::read('Users.Key.Session.resetPasswordUserId') => '00000000-0000-0000-0000-000000000001'
        ]);
        $this->Trait->expects($this->any())
            ->method('set')
            ->will($this->returnCallback(function ($param1, $param2 = null) {
                if ($param1 === 'validatePassword') {
                    TestCase::assertEquals($param2, false);
                }
            }));
        $this->Trait->changePassword();
    }

    /**
     * test
     *
     * @return void
     */
    public function testChangePasswordGetNotLoggedInOutsideResetPasswordFlow()
    {
        $this->_mockRequestGet();
        $this->_mockAuth();
        $this->_mockFlash();
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('User was not found');
        $this->Trait->changePassword();
    }

    /**
     * test
     *
     * @return void
     */
    public function testResetPassword()
    {
        $token = 'token';
        $this->Trait->expects($this->once())
                ->method('validate')
                ->with('password', $token);
        $this->Trait->resetPassword($token);
    }

    /**
     * test
     *
     * @return void
     */
    public function testRequestResetPasswordGet()
    {
        $this->assertEquals('ae93ddbe32664ce7927cf0c5c5a5e59d', $this->table->get('00000000-0000-0000-0000-000000000001')->token);
        $this->_mockRequestGet();
        $this->_mockFlash();
        $this->Trait->request->expects($this->never())
                ->method('getData');
        $this->Trait->requestResetPassword();
    }

    /**
     * test
     *
     * @return void
     */
    public function testRequestPasswordHappy()
    {
        $this->assertEquals('6614f65816754310a5f0553436dd89e9', $this->table->get('00000000-0000-0000-0000-000000000002')->token);
        $this->_mockRequestPost('post');
        $this->_mockAuthLoggedIn();
        $this->_mockFlash();
        $reference = 'user-2';
        $this->Trait->request->expects($this->once())
                ->method('getData')
                ->with('reference')
                ->will($this->returnValue($reference));
        $this->Trait->Flash->expects($this->any())
            ->method('success')
            ->with('Please check your email to continue with password reset process');
        $this->Trait->requestResetPassword();
        $this->assertNotEquals('xxx', $this->table->get('00000000-0000-0000-0000-000000000002')->token);
    }

    /**
     * test
     *
     * @return void
     */
    public function testRequestPasswordInvalidUser()
    {
        $this->_mockRequestPost('post');
        $this->_mockAuthLoggedIn(['id' => 'invalid-id', 'password' => 'invalid-pass']);
        $this->_mockFlash();
        $reference = '12312312-0000-0000-0000-000000000002';
        $this->Trait->request->expects($this->once())
                ->method('getData')
                ->with('reference')
                ->will($this->returnValue($reference));
        $this->Trait->Flash->expects($this->any())
            ->method('error')
            ->with('User 12312312-0000-0000-0000-000000000002 was not found');
        $this->Trait->requestResetPassword();
    }

    /**
     * test
     *
     * @return void
     */
    public function testRequestPasswordEmptyReference()
    {
        $this->_mockRequestPost();
        $this->_mockAuthLoggedIn(['id' => 'invalid-id', 'password' => 'invalid-pass']);
        $this->_mockFlash();
        $reference = '';
        $this->Trait->request->expects($this->once())
                ->method('getData')
                ->with('reference')
                ->will($this->returnValue($reference));
        $this->Trait->Flash->expects($this->any())
            ->method('error')
            ->with('Token could not be reset');
        $this->Trait->requestResetPassword();
    }

    /**
     * @dataProvider ensureUserActiveForResetPasswordFeature
     *
     * @return void
     */
    public function testEnsureUserActiveForResetPasswordFeature($ensureActive)
    {
        $expectError = $this->never();

        if ($ensureActive) {
            Configure::write('Users.Registration.ensureActive', true);
            $expectError = $this->once();
        }

        $this->assertEquals('ae93ddbe32664ce7927cf0c5c5a5e59d', $this->table->get('00000000-0000-0000-0000-000000000001')->token);
        $this->_mockRequestPost();
        $this->_mockFlash();
        $reference = 'user-1';
        $this->Trait->request->expects($this->once())
                ->method('getData')
                ->with('reference')
                ->will($this->returnValue($reference));
        $this->Trait->Flash->expects($expectError)
            ->method('error')
            ->with('The user is not active');
        $this->Trait->requestResetPassword();
        $this->assertNotEquals('xxx', $this->table->get('00000000-0000-0000-0000-000000000001')->token);
    }

    public function ensureUserActiveForResetPasswordFeature()
    {
        $ensureActive = true;
        $defaultBehavior = false;

        return [
            [$ensureActive],
            [$defaultBehavior]
        ];
    }

    /**
     * @dataProvider ensureGoogleAuthenticatorResets
     *
     * @return void
     */
    public function testRequestGoogleAuthTokenResetWithValidUser($userId, $method, $msg)
    {
        $this->_mockRequestPost();
        $this->_mockFlash();

        $this->Trait->Auth = $this->getMockBuilder('Cake\Controller\Component\AuthComponent')
            ->setMethods(['user', 'config'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->Trait->Auth->expects($this->never())
            ->method('user');

        $this->Trait->Flash->expects($this->at(0))
            ->method($method)
            ->with($msg, 'default');

        $this->Trait->resetGoogleAuthenticator($userId);
    }

    public function ensureGoogleAuthenticatorResets()
    {
        $error = 'error';
        $success = 'success';
        $errorMsg = 'Could not reset the token';
        $successMsg = 'Google Authenticator token was successfully reset';

        return [
            ['00000000-0000-0000-0000-000000000003', $success, $successMsg],
            ['00000000-0000-0000-0000-000000000001', $success, $successMsg],
            [null, $error, $errorMsg],
        ];
    }
}
