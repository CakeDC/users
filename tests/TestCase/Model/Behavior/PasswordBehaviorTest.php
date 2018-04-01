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

namespace CakeDC\Users\Test\TestCase\Model\Behavior;

use CakeDC\Users\Model\Behavior\PasswordBehavior;
use CakeDC\Users\Test\App\Mailer\OverrideMailer;
use Cake\Core\Configure;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;

/**
 * Test Case
 * @property \CakeDC\Users\Model\Behavior\PasswordBehavior Behavior
 */
class PasswordBehaviorTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.users',
    ];

    /**
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->table = TableRegistry::get('CakeDC/Users.Users');
        $this->Behavior = $this->getMockBuilder('CakeDC\Users\Model\Behavior\PasswordBehavior')
                ->setMethods(['sendResetPasswordEmail'])
                ->setConstructorArgs([$this->table])
                ->getMock();
        Email::setConfigTransport('test', [
            'className' => 'Debug'
        ]);
        //$this->configEmail = Email::getConfig('default');
        Email::setConfig('default', [
            'transport' => 'test',
            'from' => 'cakedc@example.com'
        ]);
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->table, $this->Behavior);
        Email::drop('default');
        Email::dropTransport('test');
        parent::tearDown();
    }

    /**
     * Test resetToken
     *
     */
    public function testResetToken()
    {
        $user = $this->table->findByUsername('user-1')->first();
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
        $user = $this->table->findByUsername('user-1')->first();
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
     * Test resetTokenNoExpiration
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Token expiration cannot be empty
     */
    public function testResetTokenNoExpiration()
    {
        $this->Behavior->resetToken('ref');
    }

    /**
     * Test resetToken
     *
     * @expectedException \CakeDC\Users\Exception\UserNotFoundException
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
     * @expectedException \CakeDC\Users\Exception\UserAlreadyActiveException
     */
    public function testResetTokenUserAlreadyActive()
    {
        $activeUser = TableRegistry::get('CakeDC/Users.Users')->findByUsername('user-4')->first();
        $this->assertTrue($activeUser->active);
        $this->table = $this->getMockForModel('CakeDC/Users.Users', ['save']);
        $this->table->expects($this->never())
                ->method('save');
        $this->Behavior->expects($this->never())
                ->method('sendResetPasswordEmail');
        $this->Behavior->resetToken('user-4', [
            'expiration' => 3600,
            'checkActive' => true,
        ]);
    }

    /**
     * Test resetToken
     *
     * @expectedException \CakeDC\Users\Exception\UserNotActiveException
     */
    public function testResetTokenUserNotActive()
    {
        $user = $this->table->findByUsername('user-1')->first();
        $this->Behavior->resetToken('user-1', [
            'ensureActive' => true,
            'expiration' => 3600
        ]);
    }

    /**
     * Test resetToken
     */
    public function testResetTokenUserActive()
    {
        $user = TableRegistry::get('CakeDC/Users.Users')->findByUsername('user-2')->first();
        $result = $this->Behavior->resetToken('user-2', [
            'ensureActive' => true,
            'expiration' => 3600
        ]);
        $this->assertEquals($user->id, $result->id);
    }

    /**
     * Test changePassword
     */
    public function testChangePassword()
    {
        $user = TableRegistry::get('CakeDC/Users.Users')->findByUsername('user-6')->first();
        $user->current_password = '12345';
        $user->password = 'new';
        $user->password_confirmation = 'new';

        $result = $this->Behavior->changePassword($user);
    }

    /**
     * test Email Override
     */
    public function testEmailOverride()
    {
        $overrideMailer = $this->getMockBuilder(OverrideMailer::class)
            ->setMethods(['send'])
            ->getMock();
        Configure::write('Users.Email.mailerClass', OverrideMailer::class);
        $this->Behavior = $this->getMockBuilder(PasswordBehavior::class)
            ->setConstructorArgs([$this->table])
            ->setMethods(['getMailer'])
            ->getMock();
        $overrideMailer->expects($this->once())
            ->method('send')
            ->with('resetPassword')
            ->willReturn(true);
        $this->Behavior->expects($this->once())
            ->method('getMailer')
            ->with(OverrideMailer::class)
            ->willReturn($overrideMailer);
        $this->Behavior->resetToken('user-1', [
            'expiration' => 3600,
            'checkActive' => true,
            'sendEmail' => true
        ]);
    }
}
