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

use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;

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
        $table = TableRegistry::get('CakeDC/Users.Users');
        $table->addBehavior('CakeDC/Users/Register.Register');
        $this->Table = $table;
        $this->Behavior = $table->behaviors()->Register;
        Email::configTransport('test', [
            'className' => 'Debug'
        ]);
        $this->Email = new Email(['from' => 'test@example.com', 'transport' => 'test']);
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Table, $this->Behavior);
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
        $result = $this->Table->register($this->Table->newEntity(), $user, ['token_expiration' => 3600, 'validate_email' => 0, 'email_class' => $this->Email]);
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
        $result = $this->Table->register($this->Table->newEntity(), $user, ['token_expiration' => 3600, 'validate_email' => 1, 'email_class' => $this->Email]);
        $this->assertFalse($result);
    }

    /**
     * Test register method
     *
     * @return void
     */
    public function testValidateRegisterValidateEmailAndTos()
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
        $result = $this->Table->register($this->Table->newEntity(), $user, ['token_expiration' => 3600, 'validate_email' => 1, 'email_class' => $this->Email]);
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
            'tos' => 1
        ];

        $this->Behavior->expects($this->never())
                ->method('getValidators');

        $entityUser = $this->Table->newEntity($user);

        $this->Behavior->expects($this->once())
                ->method('_updateActive')
                ->will($this->returnValue($entityUser));

        $this->Table->expects($this->once())
                ->method('patchEntity')
                ->with($this->Table->newEntity(), $user, ['validate' => 'custom'])
                ->will($this->returnValue($entityUser));

        $this->Table->expects($this->once())
                ->method('save')
                ->with($entityUser)
                ->will($this->returnValue($entityUser));

        $result = $this->Behavior->register($this->Table->newEntity(), $user, ['validator' => 'custom', 'validate_email' => 1, 'email_class' => $this->Email]);
        $this->assertNotEmpty($result->tos_date);
    }

    /**
     * Test register method
     *
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
        $result = $this->Table->register($this->Table->newEntity(), $user, ['token_expiration' => 3600, 'validate_email' => 1, 'use_tos' => 1, 'email_class' => $this->Email]);
        $this->assertFalse($result);
    }

    /**
     * Test register method
     *
     * @return void
     */
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
        $result = $this->Table->register($this->Table->newEntity(), $user, ['token_expiration' => 3600, 'validate_email' => 1, 'use_tos' => 0, 'email_class' => $this->Email]);
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
     * @expectedException CakeDC\Users\Exception\TokenExpiredException
     */
    public function testValidateUserWithExpiredToken()
    {
        $this->Table->validate('token-5', 'activateUser');
    }

    /**
     * Test Validate method
     *
     * @return void
     * @expectedException CakeDC\Users\Exception\UserNotFoundException
     */
    public function testValidateNotExistingUser()
    {
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
        $this->Behavior = $this->getMockBuilder('CakeDC\Users\Model\Behavior\RegisterBehavior')
                ->setMethods(['_removeValidationToken'])
                ->setConstructorArgs([$this->Table])
                ->getMock();

        $resultValidationToken = $user;
        $resultValidationToken->token_expires = null;
        $resultValidationToken->token = null;

        $this->Behavior->expects($this->once())
                ->method('_removeValidationToken')
                ->will($this->returnValue($resultValidationToken));

        $this->Behavior->activateUser($user);
    }
}
