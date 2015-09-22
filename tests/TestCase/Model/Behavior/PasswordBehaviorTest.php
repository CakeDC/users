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

namespace CakeDC\Users\Test\TestCase\Model\Behavior;

use CakeDC\Users\Exception\UserAlreadyActiveException;
use CakeDC\Users\Model\Table\UsersTable;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;

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
     * @expectedException CakeDC\Users\Exception\UserNotFoundException
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
     * @expectedException CakeDC\Users\Exception\UserAlreadyActiveException
     */
    public function testResetTokenUserAlreadyActive()
    {
        $activeUser = TableRegistry::get('CakeDC/Users.Users')->findAllByUsername('user-4')->first();
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
     * Test method
     *
     * @return void
     */
    public function testSendResetPasswordEmail()
    {
        $behavior = $this->table->behaviors()->Password;
        $this->fullBaseBackup = Router::fullBaseUrl();
        Router::fullBaseUrl('http://users.test');
        Email::configTransport('test', [
            'className' => 'Debug'
        ]);
        $this->Email = new Email([
            'from' => 'test@example.com',
            'transport' => 'test',
            'template' => 'CakeDC/Users.reset_password',
            'emailFormat' => 'both',
        ]);

        $user = $this->table->newEntity([
                'first_name' => 'FirstName',
                'email' => 'test@example.com',
                'token' => '12345'
            ]);

        $result = $behavior->sendResetPasswordEmail($user, $this->Email, 'CakeDC/Users.reset_password');
        $this->assertTextContains('From: test@example.com', $result['headers']);
        $this->assertTextContains('To: test@example.com', $result['headers']);
        $this->assertTextContains('Subject: FirstName, Your reset password link', $result['headers']);
        $this->assertTextContains('Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit

Hi FirstName,

Please copy the following address in your web browser http://users.test/users/users/reset-password/12345
Thank you,
', $result['message']);
        $this->assertTextContains('Content-Type: text/html; charset=UTF-8
Content-Transfer-Encoding: 8bit

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title>Email/html</title>
</head>
<body>
    <p>
Hi FirstName,
</p>
<p>
    <strong><a href="http://users.test/users/users/reset-password/12345">Reset your password here</a></strong>
</p>
<p>
    If the link is not correcly displayed, please copy the following address in your web browser http://users.test/users/users/reset-password/12345</p>
<p>
    Thank you,
</p>
</body>
</html>
', $result['message']);

        Router::fullBaseUrl($this->fullBaseBackup);
        Email::dropTransport('test');
    }
}
