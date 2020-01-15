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
namespace CakeDC\Users\Test\TestCase\Email;

use Cake\Mailer\Message;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Mailer\UsersMailer;

/**
 * Test Case
 */
class UsersMailerTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.SocialAccounts',
        'plugin.CakeDC/Users.Users',
    ];
    /**
     * @var UsersMailer
     */
    private $UsersMailer;

    /**
     * setUp
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->Email = $this->getMockBuilder('Cake\Mailer\Message')
            ->setMethods(['setTo', 'setSubject', 'setViewVars', 'setTemplate'])
            ->getMock();

        $this->UsersMailer = $this->getMockBuilder('CakeDC\Users\Mailer\UsersMailer')
            ->setMethods(['setViewVars'])
            ->getMock();
        $this->UsersMailer->setMessage($this->Email);
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->UsersMailer);
        unset($this->Email);
        parent::tearDown();
    }

    /**
     * test sendValidationEmail
     *
     * @return void
     */
    public function testValidation()
    {
        $this->UsersMailer = new UsersMailer();
        $table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $expectedViewVars = [
            'activationUrl' => [
                'prefix' => false,
                'plugin' => 'CakeDC/Users',
                'controller' => 'Users',
                'action' => 'validateEmail',
                '_full' => true,
                '12345678'
            ],
            'first_name' => 'FirstName',
            'last_name' => 'Bond',
            'email' => 'test@example.com',
            'token' => '12345678',
        ];

        $user = $table->newEntity([
            'first_name' => 'FirstName',
            'last_name' => 'Bond',
            'email' => 'test@example.com',
            'token' => '12345678',
        ]);
        $this->invokeMethod($this->UsersMailer, 'validation', [$user]);
        $this->assertSame(['test@example.com' => 'test@example.com'], $this->UsersMailer->getTo());
        $this->assertSame('FirstName, Your account validation link', $this->UsersMailer->getSubject());
        $this->assertSame(Message::MESSAGE_BOTH, $this->UsersMailer->getEmailFormat());
        $this->assertSame($expectedViewVars, $this->UsersMailer->viewBuilder()->getVars());
        $this->assertSame('CakeDC/Users.validation', $this->UsersMailer->viewBuilder()->getTemplate());
    }

    /**
     * test SocialAccountValidation
     *
     * @return void
     */
    public function testSocialAccountValidation()
    {
        $social = TableRegistry::getTableLocator()->get('CakeDC/Users.SocialAccounts')
            ->get('00000000-0000-0000-0000-000000000001', ['contain' => 'Users']);

        $this->Email->expects($this->once())
            ->method('setTo')
            ->with('user-1@test.com')
            ->will($this->returnValue($this->Email));

        $this->Email->expects($this->once())
            ->method('setSubject')
            ->with('first1, Your social account validation link')
            ->will($this->returnValue($this->Email));

        $this->UsersMailer->expects($this->once())
            ->method('setViewVars')
            ->with(['user' => $social->user, 'socialAccount' => $social])
            ->will($this->returnValue($this->UsersMailer));

        $this->invokeMethod($this->UsersMailer, 'socialAccountValidation', [$social->user, $social]);
    }

    /**
     * test sendValidationEmail including 'template'
     *
     * @return void
     */
    public function testResetPassword()
    {
        $this->UsersMailer = new UsersMailer();
        $table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $user = $table->newEntity([
            'first_name' => 'FirstName',
            'email' => 'test@example.com',
            'token' => '12345',
        ]);
        $expectedViewVars = [
            'activationUrl' => [
                'prefix' => false,
                'plugin' => 'CakeDC/Users',
                'controller' => 'Users',
                'action' => 'resetPassword',
                '_full' => true,
                '12345'
            ],
            'first_name' => 'FirstName',
            'email' => 'test@example.com',
            'token' => '12345',
        ];

        $this->invokeMethod($this->UsersMailer, 'resetPassword', [$user]);
        $this->assertSame(['test@example.com' => 'test@example.com'], $this->UsersMailer->getTo());
        $this->assertSame('FirstName, Your reset password link', $this->UsersMailer->getSubject());
        $this->assertSame(Message::MESSAGE_BOTH, $this->UsersMailer->getEmailFormat());
        $this->assertSame($expectedViewVars, $this->UsersMailer->viewBuilder()->getVars());
        $this->assertSame('CakeDC/Users.resetPassword', $this->UsersMailer->viewBuilder()->getTemplate());
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
