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

namespace CakeDC\Users\Test\TestCase\Model\Table;

use CakeDC\Users\Exception\AccountNotActiveException;
use CakeDC\Users\Exception\UserAlreadyActiveException;
use CakeDC\Users\Exception\UserNotFoundException;
use CakeDC\Users\Model\Table\SocialAccountsTable;
use Cake\Core\Plugin;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;

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
        'plugin.CakeDC/Users.users',
        'plugin.CakeDC/Users.social_accounts'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Users = TableRegistry::get('CakeDC/Users.Users');
        $this->fullBaseBackup = Router::fullBaseUrl();
        Router::fullBaseUrl('http://users.test');
        Email::configTransport('test', [
            'className' => 'Debug'
        ]);
        $this->configEmail = Email::config('default');
        Email::config('default', [
            'transport' => 'test',
            'from' => 'cakedc@example.com'
        ]);
        $this->Email = new Email(['from' => 'test@example.com', 'transport' => 'test']);
        Plugin::routes('CakeDC/Users');
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
        Email::drop('default');
        Email::dropTransport('test');
        Email::config('default', $this->configEmail);

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
     * test
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
        $userEntity = $this->Users->newEntity();
        $this->Users->register($userEntity, $user, ['token_expiration' => 3600, 'validate_email' => 1, 'use_tos' => 1]);
        $this->assertEquals(['tos' => ['_required' => 'This field is required']], $userEntity->errors());
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
        $user = $this->Users->find()->where(['id' => '00000000-0000-0000-0000-000000000001'])->first();
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
            'user_email' => 'user-2@test.com',
            'link' => 'link'
        ];
        $data = new \Cake\Network\Response();
        $data->provider = SocialAccountsTable::PROVIDER_FACEBOOK;
        $data->email = 'user-2@test.com';
        $data->raw = $raw;
        $data->uid = 'reference-2-1';
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
     * @expectedException CakeDC\Users\Exception\AccountNotActiveException
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
        $data = new \Cake\Network\Response();
        $data->provider = SocialAccountsTable::PROVIDER_TWITTER;
        $data->email = 'hello@test.com';
        $data->raw = $raw;
        $data->uid = 'reference-2-2';
        $data->info = [
            'first_name' => 'User 2',
        ];
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
    public function testSocialLoginCreateNewAccountWithNoCredentials()
    {
        $raw = [
            'id' => 'reference-not-existing',
            'first_name' => 'Not existing user',
            'gender' => 'male',
            'user_email' => 'user@test.com',
        ];
        $data = new \Cake\Network\Response();
        $data->provider = SocialAccountsTable::PROVIDER_TWITTER;
        $data->email = 'user@test.com';
        $data->raw = $raw;
        $data->validated = true;
        $data->uid = 'reference-not-existing';
        $data->info = [
            'first_name' => 'Not existing user',
        ];
        $data->credentials = [];
        $data->name = '';
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
            'twitter' => 'link'
        ];

        $data = new \Cake\Network\Response();
        $data->provider = SocialAccountsTable::PROVIDER_TWITTER;
        $data->email = 'user-2@test.com';
        $data->raw = $raw;
        $data->uid = 'no-existing-reference';
        $data->info = [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'urls' => ['twitter' => 'twitter'],
        ];
        $data->validated = true;

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
}
