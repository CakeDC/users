<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Model\Behavior;

use Cake\Core\Configure;
use Cake\Mailer\Email;
use Cake\Mailer\Mailer;
use Cake\Mailer\TransportFactory;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Exception\UserAlreadyActiveException;
use CakeDC\Users\Exception\UserNotActiveException;
use CakeDC\Users\Exception\UserNotFoundException;
use CakeDC\Users\Model\Behavior\PasswordBehavior;
use CakeDC\Users\Model\Entity\User;
use TestApp\Mailer\OverrideMailer;

/**
 * Test Case
 *
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
        'plugin.CakeDC/Users.Users',
    ];

    /**
     * setup
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $this->Behavior = $this->getMockBuilder('CakeDC\Users\Model\Behavior\PasswordBehavior')
                ->setMethods(['_sendResetPasswordEmail'])
                ->setConstructorArgs([$this->table])
                ->getMock();
        TransportFactory::drop('test');
        TransportFactory::setConfig('test', ['className' => 'Debug']);
        //$this->configEmail = Email::getConfig('default');
        Mailer::drop('default');
        Mailer::setConfig('default', [
            'transport' => 'test',
            'from' => 'cakedc@example.com',
        ]);
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->table, $this->Behavior);
        Email::drop('default');
        TransportFactory::drop('test');
        parent::tearDown();
    }

    /**
     * Test resetToken
     */
    public function testResetToken()
    {
        $user = $this->table->findByUsername('user-1')->first();
        $token = $user->token;
        $this->Behavior->expects($this->never())
                ->method('_sendResetPasswordEmail')
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
     */
    public function testResetTokenSendEmail()
    {
        $user = $this->table->findByUsername('user-1')->first();
        $token = $user->token;
        $tokenExpires = $user->token_expires;
        $this->Behavior->expects($this->once())
                ->method('_sendResetPasswordEmail');
        $result = $this->Behavior->resetToken('user-1', [
            'expiration' => 3600,
            'checkActive' => true,
            'sendEmail' => true,
            'type' => 'password',
        ]);
        $this->assertNotEquals($token, $result->token);
        $this->assertNotEquals($tokenExpires, $result->token_expires);
        $this->assertEmpty($result->activation_date);
        $this->assertFalse($result->active);
    }

    /**
     * Test resetToken
     */
    public function testResetTokenWithNullParams()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->Behavior->resetToken(null);
    }

    /**
     * Test resetTokenNoExpiration
     */
    public function testResetTokenNoExpiration()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Token expiration cannot be empty');
        $this->Behavior->resetToken('ref');
    }

    /**
     * Test resetToken
     */
    public function testResetTokenNotExistingUser()
    {
        $this->expectException(UserNotFoundException::class);
        $this->Behavior->resetToken('user-not-found', [
            'expiration' => 3600,
        ]);
    }

    /**
     * Test resetToken
     */
    public function testResetTokenUserAlreadyActive()
    {
        $this->expectException(UserAlreadyActiveException::class);
        $activeUser = TableRegistry::getTableLocator()->get('CakeDC/Users.Users')->findByUsername('user-4')->first();
        $this->assertTrue($activeUser->active);
        $this->table = $this->getMockForModel('CakeDC/Users.Users', ['save']);
        $this->table->expects($this->never())
                ->method('save');
        $this->Behavior->expects($this->never())
                ->method('_sendResetPasswordEmail');
        $this->Behavior->resetToken('user-4', [
            'expiration' => 3600,
            'checkActive' => true,
        ]);
    }

    /**
     * Test resetToken
     */
    public function testResetTokenUserNotActive()
    {
        $this->expectException(UserNotActiveException::class);
        $this->table->findByUsername('user-1')->firstOrFail();
        $this->Behavior->resetToken('user-1', [
            'ensureActive' => true,
            'expiration' => 3600,
        ]);
    }

    /**
     * Test resetToken
     */
    public function testResetTokenUserActive()
    {
        $user = TableRegistry::getTableLocator()->get('CakeDC/Users.Users')->findByUsername('user-2')->first();
        $result = $this->Behavior->resetToken('user-2', [
            'ensureActive' => true,
            'expiration' => 3600,
        ]);
        $this->assertEquals($user->id, $result->id);
    }

    /**
     * Test changePassword
     */
    public function testChangePassword()
    {
        $user = TableRegistry::getTableLocator()->get('CakeDC/Users.Users')->findByUsername('user-6')->first();
        $user->current_password = '12345';
        $user->password = 'new';
        $user->password_confirmation = 'new';

        $result = $this->Behavior->changePassword($user);
        $this->assertInstanceOf(User::class, $result);
        $this->assertEmpty($result->getErrors());
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
        $responseEmail = ['headers' => ['A' => 111, 'B' => 33], 'message' => 'My message' . time()];
        $overrideMailer->expects($this->once())
            ->method('send')
            ->with('resetPassword')
            ->willReturn($responseEmail);
        $this->Behavior->expects($this->once())
            ->method('getMailer')
            ->with(OverrideMailer::class)
            ->willReturn($overrideMailer);
        $result = $this->Behavior->resetToken('user-1', [
            'expiration' => 3600,
            'checkActive' => true,
            'sendEmail' => true,
            'type' => 'password',
        ]);
        $this->assertInstanceOf(User::class, $result);
    }
}
