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

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class PasswordManagementTraitIntegrationTest extends TestCase
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
     * Test login action with post request
     *
     * @return void
     */
    public function testRequestResetPassword()
    {
        $this->get('/users/request-reset-password');
        $this->assertResponseOk();
        $this->assertResponseContains('Please enter your email or username to reset your password');
        $this->assertResponseContains('<input type="text" name="reference" id="reference"/>');
    }

    /**
     * Test reset password workflow
     *
     * @return void
     */
    public function testRequestResetPasswordPostValidEmail()
    {
        $Table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $userBefore = $Table->find()->where(['email' => '4@example.com'])->firstOrFail();
        $this->assertEquals('token-4', $userBefore->token);
        $this->enableRetainFlashMessages();
        $this->enableSecurityToken();
        $data = [
            'reference' => '4@example.com',
        ];
        $this->post('/users/request-reset-password', $data);
        $this->assertRedirect('/login');
        $this->assertFlashMessage('Please check your email to continue with password reset process');
        $userAfter = $Table->find()->where(['email' => '4@example.com'])->firstOrFail();
        $this->assertNotEquals('token-4', $userAfter->token);
        $this->assertNotEmpty($userAfter->token);

        $this->get("/users/reset-password/{$userAfter->token}");
        $this->assertRedirect('/users/change-password');

        $fieldName = Configure::read('Users.Key.Session.resetPasswordUserId');
        $this->session([
            $fieldName => $userAfter->id,
        ]);
        $this->get('/users/change-password');
        $this->assertResponseOk();

        $this->assertResponseContains('<form method="post" accept-charset="utf-8" action="/users/change-password">');
        $this->assertResponseContains('Please enter the new password');
        $this->assertResponseContains('<input type="password" name="password" required="required"');
        $this->assertResponseContains('<input type="password" name="password_confirm" required="required"');
        $this->assertResponseContains('<button type="submit">Submit</button>');

        $this->post('/users/change-password', [
            'password' => '9080706050',
            'password_confirm' => '9080706050',
        ]);
        $this->assertRedirect('/login');
        $this->assertFlashMessage('Password has been changed successfully');
    }

    /**
     * Test login action with post request
     *
     * @return void
     */
    public function testRequestResetPasswordPostInvalidEmail()
    {
        $email = 'someother.un@example.com';
        $Table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $this->assertFalse($Table->exists(['email' => $email]));
        $this->enableRetainFlashMessages();
        $this->enableSecurityToken();
        $data = [
            'reference' => $email,
        ];
        $this->post('/users/request-reset-password', $data);
        $this->assertResponseOk();
        $this->assertFlashMessage('User someother.un@example.com was not found');
    }
}
