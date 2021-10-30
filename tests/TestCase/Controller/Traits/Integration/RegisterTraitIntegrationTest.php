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

namespace CakeDC\Users\Test\TestCase\Controller\Traits\Integration;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class RegisterTraitIntegrationTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.Users',
    ];

    /**
     * Test register action
     *
     * @return void
     */
    public function testRegister()
    {
        $this->get('/register');
        $this->assertResponseOk();
        $this->assertResponseContains('<form method="post" accept-charset="utf-8" action="/register">');
        $this->assertResponseContains('<legend>Add User</legend>');
        $this->assertResponseContains('<input type="text" name="username" required="required"');
        $this->assertResponseContains('<input type="email" name="email" required="required"');
        $this->assertResponseContains('<input type="password" name="password" required="required"');
        $this->assertResponseContains('<input type="password" name="password_confirm" required="required"');
        $this->assertResponseContains('<input type="text" name="first_name" id="first-name" maxlength="50"/>');
        $this->assertResponseContains('<input type="text" name="last_name" id="last-name" maxlength="50"/>');
        $this->assertResponseContains('<input type="hidden" name="tos" value="0"/>');
        $this->assertResponseContains('<label for="tos"><input type="checkbox" name="tos" value="1" required="required" id="tos" aria-required="true">Accept TOS conditions?</label>');
        $this->assertResponseContains('<button type="submit">Submit</button>');
    }

    /**
     * Test register action
     *
     * @return void
     */
    public function testRegisterPostWithErrors()
    {
        $this->enableRetainFlashMessages();
        $this->enableSecurityToken();
        $data = [
            'username' => 'user1',
            'email' => 'use1sample@example.com',
            'password' => '23423423',
            'password_confirm' => '11',
            'first_name' => '',
            'last_name' => '',
            'tos' => '0',
        ];
        $this->post('/register', $data);
        $this->assertResponseOk();
        $this->assertFlashMessage('The user could not be saved');
        $this->assertResponseNotContains('The user could not be saved');
        $this->assertResponseContains('<form method="post" accept-charset="utf-8" action="/register">');
        $this->assertResponseContains('<legend>Add User</legend>');
        $this->assertResponseContains('<input type="text" name="username" required="required"');
        $this->assertResponseContains('<input type="email" name="email" required="required"');
        $this->assertResponseContains('<input type="password" name="password" required="required"');
        $this->assertResponseContains('<input type="password" name="password_confirm" required="required"');
        $this->assertResponseContains('<input type="text" name="first_name" id="first-name" value="" maxlength="50"/>');
        $this->assertResponseContains('<input type="text" name="last_name" id="last-name" value="" maxlength="50"/>');
        $this->assertResponseContains('<input type="hidden" name="tos" value="0"/>');
        $this->assertResponseContains('<label for="tos"><input type="checkbox" name="tos" value="1" required="required" id="tos" aria-required="true">Accept TOS conditions?</label>');
        $this->assertResponseContains('<button type="submit">Submit</button>');
    }

    /**
     * Test register action
     *
     * @return void
     */
    public function testRegisterPostOkay()
    {
        $Table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $this->assertFalse($Table->exists(['username' => 'user1']));
        $this->enableRetainFlashMessages();
        $this->enableSecurityToken();
        $data = [
            'username' => 'user1',
            'email' => 'use1sample@example.com',
            'password' => '123456',
            'password_confirm' => '123456',
            'first_name' => '',
            'last_name' => '',
            'tos' => '0',
        ];
        $this->post('/register', $data);
        $this->assertRedirect('/login');
        $this->assertFlashMessage('Please validate your account before log in');
        $user = $Table->find()->where(['username' => 'user1'])->firstOrFail();
        $this->assertFalse($user->active);

        //Validate email
        $this->assertNotEmpty($user['token']);
        $url = '/users/validate-email/' . $user['token'];
        $this->get($url);
        $this->assertRedirect('/login');
        $this->assertFlashMessage('User account validated successfully');

        //If access again get error
        $this->get($url);
        $this->assertRedirect('/login');
        $this->assertFlashMessage('Token already expired');
    }

    /**
     * Test /users/validate-email without token
     *
     * @throws \Throwable
     */
    public function testValidateEmailNoToken()
    {
        $this->get('/users/validate-email');
        $this->assertRedirect('/login');
        $this->assertFlashMessage('Invalid token or user account already validated');
    }
}
