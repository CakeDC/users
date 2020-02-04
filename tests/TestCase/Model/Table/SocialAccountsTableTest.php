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

use Cake\ORM\TableRegistry;
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
        'plugin.CakeDC/Users.SocialAccounts',
        'plugin.CakeDC/Users.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->SocialAccounts = TableRegistry::getTableLocator()->get('CakeDC/Users.SocialAccounts');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
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
        $this->assertEmpty($entity->getErrors());
    }
}
