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
        $this->table = $this->getMockBuilder('Cake\ORM\Table')
                ->setMethods(['save'])
                ->getMock();
        $this->Behavior = $this->getMockBuilder('Users\Model\Behavior\PasswordBehavior')
                ->setMethods(['_getUser'])
                ->setConstructorArgs([$this->table])
                ->getMock();
        $this->user = TableRegistry::get('Users.Users')->findAllByUsername('user-1')->first();
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Behavior, $this->user);
        parent::tearDown();
    }


    /**
     * Test resetToken
     *
     */
    public function testResetToken()
    {
        $token = $this->user->token;
        $this->Behavior->expects($this->once())
                ->method('_getUser')
                ->with('user-1')
                ->will($this->returnValue($this->user));
        $this->table->expects($this->once())
                ->method('save')
                ->will($this->returnValue($this->user));
        $this->Behavior->expects($this->never())
                ->method('sendResetPasswordEmail')
                ->with($this->user);
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
        $token = $this->user->token;
        $this->Behavior->expects($this->once())
                ->method('_getUser')
                ->with('user-1')
                ->will($this->returnValue($this->user));
        $this->table->expects($this->once())
                ->method('save')
                ->will($this->returnValue($this->user));
        $this->Behavior->expects($this->once())
                ->method('sendResetPasswordEmail')
                ->with($this->user);
        $result = $this->Behavior->resetToken('user-1', [
            'expiration' => 3600,
            'checkActive' => true,
            'sendEmail' => true
        ]);
        $this->assertNotEquals($token, $result->token);
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
        $this->Behavior->expects($this->once())
                ->method('_getUser')
                ->with('user-4')
                ->will($this->returnValue($activeUser));
        $this->table->expects($this->never())
                ->method('save')
                ->will($this->returnValue($this->user));
        $this->Behavior->expects($this->never())
                ->method('sendResetPasswordEmail')
                ->with($this->user);
        $this->Behavior->resetToken('user-4', [
            'expiration' => 3600,
            'checkActive' => true,
        ]);
    }
}
