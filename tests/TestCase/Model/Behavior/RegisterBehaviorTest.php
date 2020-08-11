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
use Cake\Mailer\TransportFactory;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Exception\TokenExpiredException;
use CakeDC\Users\Exception\UserNotFoundException;
use CakeDC\Users\Model\Behavior\RegisterBehavior;

/**
 * Test Case
 */
class RegisterBehaviorTest extends TestCase
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
     * The bahavior
     *
     * @var \CakeDC\Users\Model\Behavior\RegisterBehavior
     */
    public $Behavior;

    /**
     * setup
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $table->addBehavior('CakeDC/Users/Register.Register');
        $this->Table = $table;
        $this->Behavior = $table->behaviors()->Register;
        TransportFactory::setConfig('test', ['className' => 'Debug']);
        Email::setConfig('default', [
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
        unset($this->Table, $this->Behavior);
        Email::drop('default');
        TransportFactory::drop('test');
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
        $result = $this->Table->register($this->Table->newEmptyEntity(), $user, ['token_expiration' => 3600, 'validate_email' => 0]);
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
        $result = $this->Table->register($this->Table->newEmptyEntity(), $user, ['token_expiration' => 3600, 'validate_email' => 1]);
        $this->assertFalse($result);
    }

    /**
     * Test register method
     *
     * @return void
     */
    public function testValidateRegisterValidateEmailAndTos()
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
        $result = $this->Table->register($this->Table->newEmptyEntity(), $user, ['token_expiration' => 3600, 'validate_email' => 1]);
        $this->assertNotEmpty($result);
        $this->assertFalse($result->active);
        $this->assertNotEmpty($result->tos_date);
    }

    /**
     * Test register method
     *
     * @return void
     */
    public function testValidateRegisterValidatorOption()
    {
        Router::connect('/users/validate-email/*', [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'validateEmail',
        ]);

        $this->Table = $this->getMockForModel('CakeDC/Users.Users', ['validationCustom', 'patchEntity', 'errors', 'save']);

        $this->Behavior = $this->getMockBuilder('CakeDC\Users\Model\Behavior\RegisterBehavior')
                ->setMethods(['getValidators', '_updateActive'])
                ->setConstructorArgs([$this->Table])
                ->getMock();

        $user = [
            'username' => 'testuser',
            'email' => 'testuser@test.com',
            'password' => 'password',
            'password_confirm' => 'password',
            'first_name' => 'test',
            'last_name' => 'user',
            'tos' => 1,
        ];

        $this->Behavior->expects($this->never())
                ->method('getValidators');

        $entityUser = $this->Table->newEntity($user);

        $this->Behavior->expects($this->once())
                ->method('_updateActive')
                ->will($this->returnValue($entityUser));

        $this->Table->expects($this->once())
                ->method('patchEntity')
                ->with($this->Table->newEmptyEntity(), $user, ['validate' => 'custom'])
                ->will($this->returnValue($entityUser));

        $this->Table->expects($this->once())
                ->method('save')
                ->with($entityUser)
                ->will($this->returnValue($entityUser));

        $result = $this->Behavior->register($this->Table->newEmptyEntity(), $user, ['validator' => 'custom', 'validate_email' => 1]);
        $this->assertNotEmpty($result->tos_date);
    }

    /**
     * Test register method
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
        $result = $this->Table->register($this->Table->newEmptyEntity(), $user, ['token_expiration' => 3600, 'validate_email' => 1, 'use_tos' => 1]);
        $this->assertFalse($result);
    }

    /**
     * Test register method
     *
     * @return void
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
        $result = $this->Table->register($this->Table->newEmptyEntity(), $user, ['token_expiration' => 3600, 'validate_email' => 1, 'use_tos' => 0]);
        $this->assertNotEmpty($result);
    }

    /**
     * Test ActivateUser method
     *
     * @return void
     */
    public function testActivateUser()
    {
        $user = $this->Table->find()->where(['id' => '00000000-0000-0000-0000-000000000001'])->first();
        $result = $this->Table->activateUser($user);
        $this->assertTrue($result->active);
    }

    /**
     * Test Validate method
     *
     * @return void
     */
    public function testValidate()
    {
        $result = $this->Table->validate('ae93ddbe32664ce7927cf0c5c5a5e59d', 'activateUser');
        $this->assertTrue($result->active);
        $this->assertEmpty($result->token_expires);
    }

    /**
     * Test Validate method
     *
     * @return void
     */
    public function testValidateUserWithExpiredToken()
    {
        $this->expectException(TokenExpiredException::class);
        $this->Table->validate('token-5', 'activateUser');
    }

    /**
     * Test Validate method
     *
     * @return void
     */
    public function testValidateNotExistingUser()
    {
        $this->expectException(UserNotFoundException::class);
        $this->Table->validate('not-existing-token', 'activateUser');
    }

    /**
     * Test activateUser method
     *
     * @return void
     */
    public function testActiveUserRemoveValidationToken()
    {
        $user = $this->Table->find()->where(['id' => '00000000-0000-0000-0000-000000000001'])->first();
        $this->Behavior = new RegisterBehavior($this->Table);

        $result = $this->Behavior->activateUser($user);
        $this->assertSame($result, $user);
        $this->assertNull($user->token_expires);
        $this->assertTrue($user->active);
        $this->assertInstanceOf(\DateTime::class, $user->activation_date);
        $this->assertEquals(date('Y-m-d'), $user->activation_date->format('Y-m-d'));
    }

    /**
     * Test register default role
     *
     * @return void
     */
    public function testRegisterUsingDefaultRole()
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
        Configure::write('Users.Registration.defaultRole', false);
        $result = $this->Table->register($this->Table->newEmptyEntity(), $user, [
            'token_expiration' => 3600,
            'validate_email' => 0,
        ]);
        $this->assertSame('user', $result['role']);
    }

    /**
     * Test register not default role
     *
     * @return void
     */
    public function testRegisterUsingCustomRole()
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
        Configure::write('Users.Registration.defaultRole', 'emperor');
        $result = $this->Table->register($this->Table->newEmptyEntity(), $user, [
            'token_expiration' => 3600,
            'validate_email' => 0,
        ]);
        $this->assertSame('emperor', $result['role']);
    }
}
