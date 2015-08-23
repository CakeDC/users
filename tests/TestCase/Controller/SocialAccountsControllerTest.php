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

namespace Users\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestCase;

class SocialAccountsControllerTest extends IntegrationTestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.users.social_accounts',
        'plugin.users.users'
    ];

    /**
     * test
     *
     * @return void
     */
    public function testValidateAccountHappy()
    {
        $this->get('/users/social-accounts/validate-account/Facebook/reference-1-1234/token-1234');
        $this->assertResponseSuccess();
        $this->assertRedirect(['plugin' => 'Users', 'controller' => 'Users', 'action' => 'login']);
        $this->assertSession('Account validated successfully', 'Flash.flash.message');
    }

    /**
     * test
     *
     * @return void
     */
    public function testValidateAccountInvalidToken()
    {
        $this->get('/users/social-accounts/validate-account/Facebook/reference-1-1234/token-not-found');
        $this->assertResponseSuccess();
        $this->assertRedirect(['plugin' => 'Users', 'controller' => 'Users', 'action' => 'login']);
        $this->assertSession('Invalid token and/or social account', 'Flash.flash.message');
    }

    /**
     * test
     *
     * @return void
     */
    public function testValidateAccountAlreadyActive()
    {
        $this->get('/users/social-accounts/validate-account/Twitter/reference-1-1234/token-1234');
        $this->assertResponseSuccess();
        $this->assertRedirect(['plugin' => 'Users', 'controller' => 'Users', 'action' => 'login']);
        $this->assertSession('SocialAccount already active', 'Flash.flash.message');
    }

    /**
     * test
     *
     * @return void
     */
    public function testResendValidationHappy()
    {
        $this->get('/users/social-accounts/resend-validation/Facebook/reference-1-1234');
        $this->assertResponseSuccess();
        $this->assertRedirect(['plugin' => 'Users', 'controller' => 'Users', 'action' => 'login']);
        $this->assertSession('Email sent successfully', 'Flash.flash.message');
    }

    /**
     * test
     *
     * @return void
     */
    public function testResendValidationInvalid()
    {
        $this->get('/users/social-accounts/resend-validation/Facebook/reference-invalid');
        $this->assertResponseSuccess();
        $this->assertRedirect(['plugin' => 'Users', 'controller' => 'Users', 'action' => 'login']);
        $this->assertSession('Invalid account', 'Flash.flash.message');
    }

    /**
     * test
     *
     * @return void
     */
    public function testResendValidationAlreadyActive()
    {
        $this->get('/users/social-accounts/validate-account/Twitter/reference-1-1234/token-1234');
        $this->assertResponseSuccess();
        $this->assertRedirect(['plugin' => 'Users', 'controller' => 'Users', 'action' => 'login']);
        $this->assertSession('SocialAccount already active', 'Flash.flash.message');
    }
}
