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

use CakeDC\Users\Test\TestCase\Controller\Traits\BaseTraitTest;
use Cake\Auth\PasswordHasherFactory;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

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
        $this->traitMockMethods = ['set', 'redirect', 'validate'];
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
        $this->_mockRequestPost();
        $this->_mockAuthLoggedIn();
        $this->_mockFlash();
        $this->Trait->request->expects($this->once())
                ->method('data')
                ->will($this->returnValue([
                    'password' => 'new',
                    'password_confirm' => 'new',
                ]));
        $this->Trait->expects($this->once())
                ->method('redirect')
                ->with(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'profile']);
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
        $this->_mockRequestPost();
        $this->_mockAuthLoggedIn();
        $this->_mockFlash();
        $this->Trait->request->expects($this->once())
                ->method('data')
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
    public function testChangePasswordWithInvalidUser()
    {
        $this->_mockRequestPost();
        $this->_mockAuthLoggedIn(['id' => '12312312-0000-0000-0000-000000000002', 'password' => 'invalid-pass']);
        $this->_mockFlash();
        $this->Trait->request->expects($this->once())
                ->method('data')
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
        $this->_mockRequestGet();
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
    public function testChangePasswordGetNotLoggedIn()
    {
        $this->_mockRequestGet();
        $this->_mockAuth();
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
                ->method('data');
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
        $this->_mockRequestPost();
        $this->_mockAuthLoggedIn();
        $this->_mockFlash();
        $reference = 'user-2';
        $this->Trait->request->expects($this->once())
                ->method('data')
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
        $this->_mockRequestPost();
        $this->_mockAuthLoggedIn(['id' => 'invalid-id', 'password' => 'invalid-pass']);
        $this->_mockFlash();
        $reference = '12312312-0000-0000-0000-000000000002';
        $this->Trait->request->expects($this->once())
                ->method('data')
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
                ->method('data')
                ->with('reference')
                ->will($this->returnValue($reference));
        $this->Trait->Flash->expects($this->any())
            ->method('error')
            ->with('Token could not be reset');
        $this->Trait->requestResetPassword();
    }

    /**
     * test requestResetPassword
     *
     * @return void
     */
    public function testRequestResetPasswordUserNotActive()
    {
        $this->assertEquals('ae93ddbe32664ce7927cf0c5c5a5e59d', $this->table->get('00000000-0000-0000-0000-000000000001')->token);
        $this->_mockRequestPost();
        $this->_mockFlash();
        $reference = 'user-1';
        $this->Trait->request->expects($this->once())
                ->method('data')
                ->with('reference')
                ->will($this->returnValue($reference));
        $this->Trait->Flash->expects($this->any())
            ->method('error')
            ->with('The user is not active');
        $this->Trait->requestResetPassword();
        $this->assertNotEquals('xxx', $this->table->get('00000000-0000-0000-0000-000000000001')->token);
    }
}
