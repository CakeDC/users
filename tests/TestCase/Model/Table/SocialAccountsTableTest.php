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

use Cake\Event\Event;
use Cake\Network\Email\Email;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Users\Model\Table\UsersTable;

/**
 * Users\Model\Table\UsersTable Test Case
 */
class SocialAccountsTableTest extends TestCase
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
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('SocialAccounts') ? [] : [
            'className' => 'Users\Model\Table\SocialAccountsTable'
        ];
        $this->SocialAccounts = TableRegistry::get('SocialAccounts', $config);
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
        unset($this->SocialAccounts);
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
        $result = $this->SocialAccounts->validateAccount(1, 'reference-1-1234', $token);
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
        $this->SocialAccounts->validateAccount(1, 'reference-1234', 'invalid-token');
    }

    /**
     * Test validateEmail method
     *
     * @expectedException \Cake\Datasource\Exception\RecordNotFoundException
     */
    public function testValidateEmailInvalidUser()
    {
        $this->SocialAccounts->validateAccount(1, 'invalid-user', 'token-1234');
    }

    /**
     * Test validateEmail method
     *
     * @expectedException \Users\Exception\AccountAlreadyActiveException
     */
    public function testValidateEmailActiveAccount()
    {
        $this->SocialAccounts->validateAccount(2, 'reference-1-1234', 'token-1234');
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
        $entity = $this->SocialAccounts->find()->first();
        $this->assertTrue($this->SocialAccounts->afterSave($event, $entity, []));
    }

    /**
     * testAfterSaveSocialNotActiveUserActive
     * send email here, social account is not active,
     * and user is active we need to link the account
     *
     * @return void
     */
    public function testAfterSaveSocialNotActiveUserActive()
    {
        $event = new Event('eventName');
        $entity = $this->SocialAccounts->findById(5)->first();
        $this->SocialAccounts->Users = $this->getMockForModel('Users.Users', ['getEmailInstance']);
        $this->SocialAccounts->Users->expects($this->once())
                ->method('getEmailInstance')
                ->will($this->returnValue($this->Email));
        $result = $this->SocialAccounts->afterSave($event, $entity, []);
        $this->assertTextContains('Subject: FirstName4, Your social account validation link', $result['headers']);
        unset($this->SocialAccounts->Users);
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
        $entity = $this->SocialAccounts->findById(2)->first();
        $this->assertTrue($this->SocialAccounts->afterSave($event, $entity, []));
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
        $entity = $this->SocialAccounts->findById(3)->first();
        $this->assertTrue($this->SocialAccounts->afterSave($event, $entity, []));
    }

    /**
     * Test sendSocialValidationEmail method
     *
     * @return void
     */
    public function testSendSocialValidationEmail()
    {
        $user = $this->SocialAccounts->find()->contain('Users')->first();
        $this->Email->emailFormat('both');
        $result = $this->SocialAccounts->sendSocialValidationEmail($user, $user->user, $this->Email);
        $this->assertTextContains('From: test@example.com', $result['headers']);
        $this->assertTextContains('To: user-1@test.com', $result['headers']);
        $this->assertTextContains('Subject: first1, Your social account validation link', $result['headers']);
        $this->assertTextContains('Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit

Hi first1,

Please copy the following address in your web browser to activate your social login http://users.test/users/social-accounts/validate-account/1/reference-1-1234/token-1234
Thank you,
', $result['message']);
        $this->assertTextContains('Hi first1,', $result['message']);
        $this->assertTextContains('<a href="http://users.test/users/social-accounts/validate-account/1/reference-1-1234/token-1234">Activate your social login here</a>', $result['message']);
        $this->assertTextContains('If the link is not correcly displayed, please copy the following address in your web browser http://users.test/users/social-accounts/validate-account/1/reference-1-1234/token-1234', $result['message']);
    }
}
