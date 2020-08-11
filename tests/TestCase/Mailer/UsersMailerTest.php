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
use CakeDC\Users\Model\Entity\User;

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
        $this->UsersMailer = new UsersMailer();
        parent::setUp();
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->UsersMailer);
        parent::tearDown();
    }

    /**
     * test sendValidationEmail
     *
     * @return void
     */
    public function testValidation()
    {
        $table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $expectedViewVars = [
            'activationUrl' => [
                'prefix' => false,
                'plugin' => 'CakeDC/Users',
                'controller' => 'Users',
                'action' => 'validateEmail',
                '_full' => true,
                '12345678',
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
        $this->assertInstanceOf(User::class, $social->user);
        $expectedViewVars = [
            'user' => $social->user,
            'socialAccount' => $social,
            'activationUrl' => [
                '_full' => true,
                'prefix' => false,
                'plugin' => 'CakeDC/Users',
                'controller' => 'SocialAccounts',
                'action' => 'validateAccount',
                'Facebook',
                'reference-1-1234',
                'token-1234',
            ],
        ];

        $this->invokeMethod($this->UsersMailer, 'socialAccountValidation', [$social->user, $social]);
        $this->assertSame(['user-1@test.com' => 'user-1@test.com'], $this->UsersMailer->getTo());
        $this->assertSame('first1, Your social account validation link', $this->UsersMailer->getSubject());
        $this->assertSame(Message::MESSAGE_BOTH, $this->UsersMailer->getEmailFormat());
        $this->assertSame($expectedViewVars, $this->UsersMailer->viewBuilder()->getVars());
        $this->assertSame('CakeDC/Users.socialAccountValidation', $this->UsersMailer->viewBuilder()->getTemplate());
    }

    /**
     * test sendValidationEmail including 'template'
     *
     * @return void
     */
    public function testResetPassword()
    {
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
                '12345',
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
