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
namespace CakeDC\Users\Test\TestCase\Email;

use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;

/**
 * Test Case
 */
class EmailSenderTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.social_accounts',
        'plugin.CakeDC/Users.users',
    ];

    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->EmailSender = $this->getMockBuilder('CakeDC\Users\Email\EmailSender')
            ->setMethods(['_getEmailInstance', 'getMailer'])
            ->getMock();

        $this->UserMailer = $this->getMockBuilder('CakeDC\Users\Mailer\UsersMailer')
            ->setMethods(['send'])
            ->getMock();

        $this->fullBaseBackup = Router::fullBaseUrl();
        Router::fullBaseUrl('http://users.test');

        Email::setConfigTransport('test', [
            'className' => 'Debug'
        ]);
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        Email::drop('default');
        Email::dropTransport('test');
        parent::tearDown();
    }

    /**
     * test sendValidationEmail
     *
     * @return void
     */
    public function testSendEmailValidation()
    {
        $table = TableRegistry::get('CakeDC/Users.Users');
        $user = $table->newEntity([
            'first_name' => 'FirstName',
            'email' => 'test@example.com',
            'token' => '12345'
        ]);

        $email = new Email([
            'from' => 'test@example.com',
            'transport' => 'test',
            'emailFormat' => 'both',
        ]);

        $this->EmailSender->expects($this->once())
            ->method('getMailer')
            ->with('CakeDC/Users.Users')
            ->will($this->returnValue($this->UserMailer));

        $this->UserMailer->expects($this->once())
            ->method('send')
            ->with('validation', [$user, 'Your account validation link']);

        $this->EmailSender->sendValidationEmail($user, $email);
    }

    /**
     * test sendResetPasswordEmail
     *
     * @return void
     */
    public function testSendResetPasswordEmailMailer()
    {
        $table = TableRegistry::get('CakeDC/Users.Users');
        $user = $table->newEntity([
            'first_name' => 'FirstName',
            'email' => 'test@example.com',
            'token' => '12345'
        ]);

        $email = new Email([
            'from' => 'test@example.com',
            'transport' => 'test',
            'template' => 'CakeDC/Users.reset_password',
            'emailFormat' => 'both',
        ]);

        $this->EmailSender->expects($this->once())
            ->method('getMailer')
            ->with('CakeDC/Users.Users')
            ->will($this->returnValue($this->UserMailer));

        $this->UserMailer->expects($this->once())
            ->method('send')
            ->with('resetPassword', [$user, 'CakeDC/Users.reset_password']);

        $this->EmailSender->sendResetPasswordEmail($user, $email);
    }

    /**
     * test sendSocialValidationEmail
     *
     * @return void
     */
    public function testSendSocialValidationEmailMailer()
    {
        $this->Table = TableRegistry::get('CakeDC/Users.SocialAccounts');
        $user = $this->Table->find()->contain('Users')->first();
        $email = new Email([
            'from' => 'test@example.com',
            'transport' => 'test',
            'template' => 'CakeDC/Users.my_template',
            'emailFormat' => 'both',
        ]);

        $this->EmailSender->expects($this->once())
            ->method('getMailer')
            ->with('CakeDC/Users.Users')
            ->will($this->returnValue($this->UserMailer));

        $this->UserMailer->expects($this->once())
            ->method('send')
            ->with('socialAccountValidation', [$user->user, $user, 'CakeDC/Users.my_template']);

        $this->EmailSender->sendSocialValidationEmail($user, $user->user, $email);
    }
}
