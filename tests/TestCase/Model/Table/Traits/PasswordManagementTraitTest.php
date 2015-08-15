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
use Users\Model\Table\Traits\PasswordManagementTrait;

/**
 * Test Case
 */
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
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Trait = $this->getMockBuilder('Users\Model\Table\Traits\PasswordManagementTrait')
                ->setMethods(['_getUser', 'save', 'sendResetPasswordEmail'])
                ->getMockForTrait();
        $this->user = TableRegistry::get('Users.Users')->findAllByUsername('user-1')->first();
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Trait, $this->user);
        parent::tearDown();
    }


    /**
     * Test resetToken
     *
     */
    public function testResetToken()
    {
        $token = $this->user->token;
        $this->Trait->expects($this->once())
                ->method('_getUser')
                ->with('user-1')
                ->will($this->returnValue($this->user));
        $this->Trait->expects($this->once())
                ->method('save')
                ->will($this->returnValue($this->user));
        $this->Trait->expects($this->never())
                ->method('sendResetPasswordEmail')
                ->with($this->user);
        $result = $this->Trait->resetToken('user-1', [
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
        $this->Trait->expects($this->once())
                ->method('_getUser')
                ->with('user-1')
                ->will($this->returnValue($this->user));
        $this->Trait->expects($this->once())
                ->method('save')
                ->will($this->returnValue($this->user));
        $this->Trait->expects($this->once())
                ->method('sendResetPasswordEmail')
                ->with($this->user);
        $result = $this->Trait->resetToken('user-1', [
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
        $this->Trait->resetToken(null);
    }

    /**
     * Test resetToken
     *
     * @expectedException Users\Exception\UserNotFoundException
     */
    public function testResetTokenNotExistingUser()
    {
        $this->Trait->resetToken('user-not-found', [
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
        $this->Trait->expects($this->once())
                ->method('_getUser')
                ->with('user-4')
                ->will($this->returnValue($activeUser));
        $this->Trait->expects($this->never())
                ->method('save')
                ->will($this->returnValue($this->user));
        $this->Trait->expects($this->never())
                ->method('sendResetPasswordEmail')
                ->with($this->user);
        $this->Trait->resetToken('user-4', [
            'expiration' => 3600,
            'checkActive' => true,
        ]);
    }
}
