<?php
/**
 * Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
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
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Table, $this->Behavior, $this->Email);
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
}
