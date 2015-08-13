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

namespace Users\Test\TestCase\Model\Table;

use Cake\Network\Email\Email;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use Opauth\Opauth\Response;
use Users\Exception\AccountNotActiveException;
use Users\Exception\UserAlreadyActiveException;
use Users\Exception\UserNotFoundException;
use Users\Model\Table\SocialAccountsTable;

/**
 * Users\Model\Table\UsersTable Test Case
 */
class UsersTableTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.users.users',
        'plugin.users.social_accounts'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Users = TableRegistry::get('Users.Users');
        $this->fullBaseBackup = Router::fullBaseUrl();
        Router::fullBaseUrl('http://users.test');
        Email::configTransport('test', [
            'className' => 'Debug'
        ]);
        $this->Email = new Email(['from' => 'test@example.com', 'transport' => 'test']);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Users);
        Router::fullBaseUrl($this->fullBaseBackup);
        Email::dropTransport('test');

        parent::tearDown();
    }

    /**
     * Test register method
     *
     * @return void
     */
    public function testValidateRegisterNoValidateEmail()
    {
        $user = [
            'username' => 'testuser',
            'email' => 'testuser@test.com',
            'password' => 'password',
            'password_confirm' => 'password',
            'first_name' => 'test',
            'last_name' => 'user',
            'tos' => 1
        ];
        $result = $this->Users->register($this->Users->newEntity(), $user, ['token_expiration' => 3600, 'validate_email' => 0]);
        $this->assertTrue($result->active);
    }

    /**
     * Test register method
     *
     * @return void
     */
    public function testValidateRegisterEmptyUser()
    {
        $user = [];
        $result = $this->Users->register($this->Users->newEntity(), $user, ['token_expiration' => 3600, 'validate_email' => 1]);
        $this->assertFalse($result);
    }

    /**
     * Test register method
     *
     * @return void
     */
    public function testValidateRegisterValidateEmail()
    {
        $user = [
            'username' => 'testuser',
            'email' => 'testuser@test.com',
            'password' => 'password',
            'password_confirm' => 'password',
            'first_name' => 'test',
            'last_name' => 'user',
            'tos' => 1
        ];
        $result = $this->Users->register($this->Users->newEntity(), $user, ['token_expiration' => 3600, 'validate_email' => 1]);
        $this->assertNotEmpty($result);
        $this->assertFalse($result->active);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testValidateRegisterTosRequired()
    {
        $user = [
            'username' => 'testuser',
            'email' => 'testuser@test.com',
            'password' => 'password',
            'password_confirm' => 'password',
            'first_name' => 'test',
            'last_name' => 'user',
        ];
        $this->Users->register($this->Users->newEntity(), $user, ['token_expiration' => 3600, 'validate_email' => 1, 'use_tos' => 1]);
    }

    /**
     * Test register method
    testValidateRegisterValidateEmail     */
    public function testValidateRegisterNoTosRequired()
    {
        $user = [
            'username' => 'testuser',
            'email' => 'testuser@test.com',
            'password' => 'password',
            'password_confirm' => 'password',
            'first_name' => 'test',
            'last_name' => 'user',
        ];
        $result = $this->Users->register($this->Users->newEntity(), $user, ['token_expiration' => 3600, 'validate_email' => 1, 'use_tos' => 0]);
        $this->assertNotEmpty($result);
    }

    /**
     * Test ActivateUser method
     *
     * @return void
     */
    public function testActivateUser()
    {
        $user = $this->Users->find()->where(['id' => 1])->first();
        $result = $this->Users->activateUser($user);
        $this->assertTrue($result->active);
    }

    public function testSocialLogin()
    {
        $raw = [
            'id' => 'reference-2-1',
            'first_name' => 'User 2',
            'gender' => 'female',
            'verified' => 1,
            'user_email' => 'hello@test.com',
        ];
        $data = new Response(SocialAccountsTable::PROVIDER_FACEBOOK, $raw);
        $data->setData('uid', 'id');
        $options = [
            'use_email' => 1,
            'validate_email' => 1,
            'token_expiration' => 3600
        ];
        $result = $this->Users->socialLogin($data, $options);
        $this->assertEquals('user-2@test.com', $result->email);
        $this->assertTrue($result->active);
    }

    /**
     * Test socialLogin
     *
     * @expectedException \Users\Exception\AccountNotActiveException
     */
    public function testSocialLoginInactiveAccount()
    {
        $raw = [
            'id' => 'reference-2-2',
            'first_name' => 'User 2',
            'gender' => 'female',
            'verified' => 1,
            'user_email' => 'hello@test.com',
        ];
        $data = new Response(SocialAccountsTable::PROVIDER_TWITTER, $raw);
        $data->setData('uid', 'id');
        $options = [
            'use_email' => 1,
            'validate_email' => 1,
            'token_expiration' => 3600
        ];
        $result = $this->Users->socialLogin($data, $options);
        $this->assertEquals('user-2@test.com', $result->email);
        $this->assertFalse($result->active);
    }

    /**
     * Test socialLogin
     *
     * @expectedException InvalidArgumentException
     */
    public function testSocialLoginddCreateNewAccountWithNoCredentials()
    {
        $raw = [
            'id' => 'reference-not-existing',
            'first_name' => 'Not existing user',
            'gender' => 'male',
            'user_email' => 'user@test.com',
        ];
        $data = new Response(SocialAccountsTable::PROVIDER_TWITTER, $raw);
        $data->setData('uid', 'id');
        $options = [
            'use_email' => 0,
            'validate_email' => 1,
            'token_expiration' => 3600
        ];
        $result = $this->Users->socialLogin($data, $options);
        $this->assertFalse($result);
    }

    /**
     * Test socialLogin
     *
     */
    public function testSocialLoginCreateNewAccount()
    {
        $raw = [
            'id' => 'no-existing-reference',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'gender' => 'male',
            'user_email' => 'user@test.com',
        ];

        $data = new Response(SocialAccountsTable::PROVIDER_TWITTER, $raw);
        $data->setData('uid', 'id');
        $data->setData('info.first_name', 'first_name');
        $data->setData('info.last_name', 'last_name');
        $data->email = 'username@test.com';
        $data->credentials = [
            'token' => 'token',
            'token_secret' => 'secret',
            'token_expires' => ''
        ];
        $options = [
            'use_email' => 0,
            'validate_email' => 0,
            'token_expiration' => 3600
        ];
        $result = $this->Users->socialLogin($data, $options);
        $this->assertNotEmpty($result);
        $this->assertEquals('no-existing-reference', $result->social_accounts[0]->reference);
        $this->assertEquals(1, count($result->social_accounts));
        $this->assertEquals('username', $result->username);
        $this->assertEquals('First Name', $result->first_name);
        $this->assertEquals('Last Name', $result->last_name);
    }

    /**
     * Test sendValidationEmail method
     *
     * @return void
     */
    public function testSendValidationEmail()
    {
        $user = $this->Users->newEntity([
                'first_name' => 'FirstName',
                'email' => 'test@example.com',
                'token' => '12345'
            ]);
        $this->Email->template('Users.validation')
            ->emailFormat('both');

        $result = $this->Users->sendValidationEmail($user, $this->Email);
        $this->assertTextContains('From: test@example.com', $result['headers']);
        $this->assertTextContains('To: test@example.com', $result['headers']);
        $this->assertTextContains('Subject: FirstName, Your account validation link', $result['headers']);
        $this->assertTextContains('Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit

Hi FirstName,

Please copy the following address in your web browser http://users.test/users/users/validate-email/12345
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
    <strong><a href="http://users.test/users/users/validate-email/12345">Activate your account here</a></strong>
</p>
<p>
    If the link is not correcly displayed, please copy the following address in your web browser http://users.test/users/users/validate-email/12345</p>
<p>
    Thank you,
</p>
</body>
</html>
', $result['message']);
    }

    /**
     * Test method
     *
     * @return void
     */
    public function testSendResetPasswordEmail()
    {
        $user = $this->Users->newEntity([
                'first_name' => 'FirstName',
                'email' => 'test@example.com',
                'token' => '12345'
            ]);
        $this->Email->template('Users.reset_password')
            ->emailFormat('both');

        $result = $this->Users->sendResetPasswordEmail($user, $this->Email);
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
    }

    /**
     * testGetEmailInstance
     *
     * @return void
     */
    public function testGetEmailInstance()
    {
        $email = $this->Users->getEmailInstance();
        $this->assertInstanceOf('Cake\Network\Email\Email', $email);
        $this->assertEquals([
            'template' => 'Users.validation',
            'layout' => 'default'
        ], $email->template());
    }

    /**
     * testGetEmailInstanceOverrideEmail
     *
     * @return void
     */
    public function testGetEmailInstanceOverrideEmail()
    {
        $email = new Email();
        $email->template('another_template');
        $email = $this->Users->getEmailInstance($email);
        $this->assertInstanceOf('Cake\Network\Email\Email', $email);
        $this->assertEquals([
            'template' => 'another_template',
            'layout' => 'default'
        ], $email->template());
    }
}
