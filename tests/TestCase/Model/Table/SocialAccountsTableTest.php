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

use CakeDC\Users\Model\Table\SocialAccountsTable;
use CakeDC\Users\Model\Table\UsersTable;
use Cake\Event\Event;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;

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
        'plugin.CakeDC/Users.social_accounts',
        'plugin.CakeDC/Users.users'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->SocialAccounts = TableRegistry::get('CakeDC/Users.SocialAccounts');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->SocialAccounts);

        parent::tearDown();
    }

    public function testValidationHappy()
    {
        $data = [
            'provider' => 'Facebook',
            'reference' => 'test-reference',
            'link' => 'test-link',
            'token' => 'test-token',
            'active' => 0,
            'data' => 'test-data',
        ];
        $entity = $this->SocialAccounts->newEntity($data);
        $this->assertEmpty($entity->errors());
    }
}
