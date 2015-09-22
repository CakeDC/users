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

use CakeDC\Users\Model\Table\SocialAccountsTable;
use Cake\Event\Event;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;

/**
 * Test Case
 */
class SocialAccountBehaviorTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.social_accounts',
        'plugin.CakeDC/Users.users'
    ];

    /**
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Table = TableRegistry::get('CakeDC/Users.SocialAccounts');
        $this->Behavior = $this->Table->behaviors()->SocialAccount;
        $this->fullBaseBackup = Router::fullBaseUrl();
        Router::fullBaseUrl('http://users.test');
        Email::configTransport('test', [
            'className' => 'Debug'
        ]);
        $this->Email = new Email([
            'from' => 'test@example.com',
            'transport' => 'test',
            'template' => 'CakeDC/Users.social_account_validation',
        ]);
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Table, $this->Behavior, $this->Email);
        Router::fullBaseUrl($this->fullBaseBackup);
        Email::dropTransport('test');
        parent::tearDown();
    }

    /**
     * Test validateEmail method
     *
     * @return void
     */
    public function testValidateEmail()
    {
        $token = 'token-1234';
        $result = $this->Behavior->validateAccount(SocialAccountsTable::PROVIDER_FACEBOOK, 'reference-1-1234', $token);
        $this->assertTrue($result->active);
        $this->assertEquals($token, $result->token);
    }

    /**
     * Test validateEmail method
     *
     * @expectedException \Cake\Datasource\Exception\RecordNotFoundException
     */
    public function testValidateEmailInvalidToken()
    {
        $this->Behavior->validateAccount(1, 'reference-1234', 'invalid-token');
    }

    /**
     * Test validateEmail method
     *
     * @expectedException \Cake\Datasource\Exception\RecordNotFoundException
     */
    public function testValidateEmailInvalidUser()
    {
        $this->Behavior->validateAccount(1, 'invalid-user', 'token-1234');
    }

    /**
     * Test validateEmail method
     *
     * @expectedException CakeDC\Users\Exception\AccountAlreadyActiveException
     */
    public function testValidateEmailActiveAccount()
    {
        $this->Behavior->validateAccount(SocialAccountsTable::PROVIDER_TWITTER, 'reference-1-1234', 'token-1234');
    }

    /**
     * testAfterSaveSocialNotActiveUserNotActive
     * don't send email, user is not active
     *
     * @return void
     */
    public function testAfterSaveSocialNotActiveUserNotActive()
    {
        $event = new Event('eventName');
        $entity = $this->Table->find()->first();
        $this->assertTrue($this->Behavior->afterSave($event, $entity, []));
    }

    /**
     * testAfterSaveSocialActiveUserActive
     * social account is active, don't send email
     *
     * @return void
     */
    public function testAfterSaveSocialActiveUserActive()
    {
        $event = new Event('eventName');
        $entity = $this->Table->findById('00000000-0000-0000-0000-000000000003')->first();
        $this->assertTrue($this->Behavior->afterSave($event, $entity, []));
    }

    /**
     * testAfterSaveSocialActiveUserNotActive
     * social account is active, don't send email
     *
     * @return void
     */
    public function testAfterSaveSocialActiveUserNotActive()
    {
        $event = new Event('eventName');
        $entity = $this->Table->findById('00000000-0000-0000-0000-000000000002')->first();
        $this->assertTrue($this->Behavior->afterSave($event, $entity, []));
    }

    /**
     * Test sendSocialValidationEmail method
     *
     * @return void
     */
    public function testSendSocialValidationEmail()
    {
        $user = $this->Table->find()->contain('Users')->first();
        $this->Email->emailFormat('both');
        $result = $this->Behavior->sendSocialValidationEmail($user, $user->user, $this->Email);
        $this->assertTextContains('From: test@example.com', $result['headers']);
        $this->assertTextContains('To: user-1@test.com', $result['headers']);
        $this->assertTextContains('Subject: first1, Your social account validation link', $result['headers']);
        $this->assertTextContains('Hi first1,', $result['message']);
        $this->assertTextContains('<a href="http://users.test/users/social-accounts/validate-account/Facebook/reference-1-1234/token-1234">Activate your social login here</a>', $result['message']);
        $this->assertTextContains('If the link is not correcly displayed, please copy the following address in your web browser http://users.test/users/social-accounts/validate-account/Facebook/reference-1-1234/token-1234', $result['message']);
    }
}
