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

use Cake\Auth\PasswordHasherFactory;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class PasswordManagementTraitTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.users.users',
    ];

    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->table = TableRegistry::get('Users.Users');
        $this->Trait = $this->getMockBuilder('Users\Controller\Traits\PasswordManagementTrait')
                ->setMethods(['set', 'getUsersTable', 'redirect', 'validate'])
                ->getMockForTrait();
        $this->Trait->expects($this->any())
                ->method('getUsersTable')
                ->will($this->returnValue($this->table));
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
                ->setMethods(['is'])
                ->getMock();
        $this->Trait->request->expects($this->once())
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
     * test
     *
     * @return void
     */
    public function testChangePasswordHappy()
    {
        $this->assertEquals('12345', $this->table->get(1)->password);
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
                ->with(['admin' => false, 'plugin' => 'Users', 'controller' => 'users', 'action' => 'profile']);
        $this->Trait->Flash->expects($this->any())
            ->method('success')
            ->with('Password has been changed successfully');
        $this->Trait->changePassword();
        $hasher = PasswordHasherFactory::build('Default');
        $this->assertTrue($hasher->check('new', $this->table->get(1)->password));
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
        $this->assertEquals('xxx', $this->table->get(1)->token);
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
        $this->assertEquals('xxx', $this->table->get(1)->token);
        $this->_mockRequestPost();
        $this->_mockAuthLoggedIn();
        $this->_mockFlash();
        $reference = 'user-1';
        $this->Trait->request->expects($this->once())
                ->method('data')
                ->with('reference')
                ->will($this->returnValue($reference));
        $this->Trait->Flash->expects($this->any())
            ->method('success')
            ->with('Password has been changed successfully');
        $this->Trait->requestResetPassword();
        $this->assertNotEquals('xxx', $this->table->get(1)->token);
    }
}
