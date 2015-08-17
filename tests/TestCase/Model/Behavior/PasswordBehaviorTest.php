<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Users\Test\TestCase\Model\Table\Traits;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use Users\Exception\UserAlreadyActiveException;
use Users\Model\Table\UsersTable;

/**
 * Test Case
 */
class PasswordBehaviorTest extends TestCase
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
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->table = TableRegistry::get('Users.Users');
        $this->Behavior = $this->getMockBuilder('Users\Model\Behavior\PasswordBehavior')
                ->setMethods(['sendResetPasswordEmail'])
                ->setConstructorArgs([$this->table])
                ->getMock();
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->table, $this->Behavior);
        parent::tearDown();
    }


    /**
     * Test resetToken
     *
     */
    public function testResetToken()
    {
        $user = $this->table->findAllByUsername('user-1')->first();
        $token = $user->token;
        $this->Behavior->expects($this->never())
                ->method('sendResetPasswordEmail')
                ->with($user);
        $result = $this->Behavior->resetToken('user-1', [
            'expiration' => 3600,
            'checkActive' => true,
        ]);
        $this->assertNotEquals($token, $result->token);
        $this->assertEmpty($result->activation_date);
        $this->assertFalse($result->active);
    }

    /**
     * Test resetToken
     *
     */
    public function testResetTokenSendEmail()
    {
        $user = $this->table->findAllByUsername('user-1')->first();
        $token = $user->token;
        $tokenExpires = $user->token_expires;
        $this->Behavior->expects($this->once())
                ->method('sendResetPasswordEmail');
        $result = $this->Behavior->resetToken('user-1', [
            'expiration' => 3600,
            'checkActive' => true,
            'sendEmail' => true
        ]);
        $this->assertNotEquals($token, $result->token);
        $this->assertNotEquals($tokenExpires, $result->token_expires);
        $this->assertEmpty($result->activation_date);
        $this->assertFalse($result->active);
    }

    /**
     * Test resetToken
     *
     * @expectedException InvalidArgumentException
     */
    public function testResetTokenWithNullParams()
    {
        $this->Behavior->resetToken(null);
    }

    /**
     * Test resetToken
     *
     * @expectedException Users\Exception\UserNotFoundException
     */
    public function testResetTokenNotExistingUser()
    {
        $this->Behavior->resetToken('user-not-found', [
            'expiration' => 3600
        ]);
    }

    /**
     * Test resetToken
     *
     * @expectedException Users\Exception\UserAlreadyActiveException
     */
    public function testResetTokenUserAlreadyActive()
    {
        $activeUser = TableRegistry::get('Users.Users')->findAllByUsername('user-4')->first();
        $this->assertTrue($activeUser->active);
        $this->table = $this->getMockForModel('Users.Users', ['save']);
        $this->table->expects($this->never())
                ->method('save');
        $this->Behavior->expects($this->never())
                ->method('sendResetPasswordEmail');
        $this->Behavior->resetToken('user-4', [
            'expiration' => 3600,
            'checkActive' => true,
        ]);
    }
}
