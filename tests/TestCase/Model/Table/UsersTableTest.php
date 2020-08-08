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

namespace CakeDC\Users\Test\TestCase\Model\Table;

use Cake\Mailer\Email;
use Cake\Mailer\TransportFactory;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Exception\AccountNotActiveException;
use CakeDC\Users\Model\Table\SocialAccountsTable;

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
        'plugin.CakeDC/Users.Users',
        'plugin.CakeDC/Users.SocialAccounts',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->Users = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $this->fullBaseBackup = Router::fullBaseUrl();
        Router::fullBaseUrl('http://users.test');
        TransportFactory::drop('test');
        TransportFactory::setConfig('test', ['className' => 'Debug']);
        Email::setConfig('default', [
            'transport' => 'test',
            'from' => 'cakedc@example.com',
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Users);
        Router::fullBaseUrl($this->fullBaseBackup);
        Email::drop('default');

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
            'tos' => 1,
        ];
        $result = $this->Users->register($this->Users->newEmptyEntity(), $user, ['token_expiration' => 3600, 'validate_email' => 0]);
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
        $result = $this->Users->register($this->Users->newEmptyEntity(), $user, ['token_expiration' => 3600, 'validate_email' => 1]);
        $this->assertFalse($result);
    }

    /**
     * Test register method
     *
     * @return void
     */
    public function testValidateRegisterValidateEmail()
    {
        Router::connect('/users/validate-email/*', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'validateEmail',
        ]);

        $user = [
            'username' => 'testuser',
            'email' => 'testuser@test.com',
            'password' => 'password',
            'password_confirm' => 'password',
            'first_name' => 'test',
            'last_name' => 'user',
            'tos' => 1,
        ];
        $result = $this->Users->register($this->Users->newEmptyEntity(), $user, ['token_expiration' => 3600, 'validate_email' => 1]);
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
        $userEntity = $this->Users->newEmptyEntity();
        $this->Users->register($userEntity, $user, ['token_expiration' => 3600, 'validate_email' => 1, 'use_tos' => 1]);
        $this->assertEquals(['tos' => ['_required' => 'This field is required']], $userEntity->getErrors());
    }

    /**
     * Test register method
     * testValidateRegisterValidateEmail
     */
    public function testValidateRegisterNoTosRequired()
    {
        Router::connect('/users/validate-email/*', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'validateEmail',
        ]);

        $user = [
            'username' => 'testuser',
            'email' => 'testuser@test.com',
            'password' => 'password',
            'password_confirm' => 'password',
            'first_name' => 'test',
            'last_name' => 'user',
        ];
        $result = $this->Users->register($this->Users->newEmptyEntity(), $user, ['token_expiration' => 3600, 'validate_email' => 1, 'use_tos' => 0]);
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
        $data = [
            'provider' => SocialAccountsTable::PROVIDER_FACEBOOK,
            'email' => 'user-2@test.com',
            'id' => 'reference-2-1',
            'link' => 'link',
            'raw' => [
                'id' => 'reference-2-1',
                'token' => 'token',
                'first_name' => 'User 2',
                'gender' => 'female',
                'verified' => 1,
                'user_email' => 'user-2@test.com',
                'link' => 'link',
            ],
        ];
        $options = [
            'use_email' => 1,
            'validate_email' => 1,
            'token_expiration' => 3600,
        ];
        $result = $this->Users->socialLogin($data, $options);
        $this->assertEquals('user-2@test.com', $result->email);
        $this->assertTrue($result->active);
    }

    /**
     * Test socialLogin
     */
    public function testSocialLoginInactiveAccount()
    {
        $this->expectException(AccountNotActiveException::class);
        $data = [
            'provider' => SocialAccountsTable::PROVIDER_TWITTER,
            'email' => 'hello@test.com',
            'id' => 'reference-2-2',
            'link' => 'link',
            'raw' => [
                'id' => 'reference-2-2',
                'first_name' => 'User 2',
                'gender' => 'female',
                'verified' => 1,
                'user_email' => 'hello@test.com',
            ],
        ];
        $options = [
            'use_email' => 1,
            'validate_email' => 1,
            'token_expiration' => 3600,
        ];
        $result = $this->Users->socialLogin($data, $options);
        $this->assertEquals('user-2@test.com', $result->email);
        $this->assertFalse($result->active);
    }

    /**
     * Test socialLogin
     */
    public function testSocialLoginCreateNewAccountWithNoCredentials()
    {
        $this->expectException(\InvalidArgumentException::class);
        $data = [
            'provider' => SocialAccountsTable::PROVIDER_TWITTER,
            'email' => 'user@test.com',
            'id' => 'reference-not-existing',
            'link' => 'link',
            'raw' => [
                'id' => 'reference-not-existing',
                'first_name' => 'Not existing user',
                'gender' => 'male',
                'user_email' => 'user@test.com',
            ],
            'credentials' => [],
            'name' => '',
        ];

        $options = [
            'use_email' => 0,
            'validate_email' => 1,
            'token_expiration' => 3600,
        ];
        $result = $this->Users->socialLogin($data, $options);
        $this->assertFalse($result);
    }

    /**
     * Test socialLogin
     */
    public function testSocialLoginCreateNewAccount()
    {
        $data = [
            'provider' => SocialAccountsTable::PROVIDER_TWITTER,
            'email' => 'username@test.com',
            'id' => 'no-existing-reference',
            'link' => 'link',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'raw' => [
                'id' => 'no-existing-reference',
                'first_name' => 'First Name',
                'last_name' => 'Last Name',
                'gender' => 'male',
                'user_email' => 'user@test.com',
                'twitter' => 'link',
            ],
            'info' => [
                'first_name' => 'First Name',
                'last_name' => 'Last Name',
                'urls' => ['twitter' => 'twitter'],
            ],
            'validated' => true,
            'credentials' => [
                'token' => 'token',
                'token_secret' => 'secret',
                'token_expires' => '',
            ],
        ];

        $options = [
            'use_email' => 0,
            'validate_email' => 0,
            'token_expiration' => 3600,
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
