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

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use CakeDC\Users\Model\Behavior\AuthFinderBehavior;

/**
 * Test Case
 */
class AuthFinderBehaviorTest extends TestCase
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
     * setup
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $this->Behavior = new AuthFinderBehavior($this->table);
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->table, $this->Behavior);
        parent::tearDown();
    }

    /**
     * Test findActive method.
     */
    public function testFindActive()
    {
        $actual = $this->table->find('active')->toArray();
        $this->assertCount(8, $actual);
        $this->assertCount(8, Hash::extract($actual, '{n}[active=1]'));
        $this->assertCount(0, Hash::extract($actual, '{n}[active=0]'));
    }

    /**
     * Test findAuth method.
     */
    public function testFindAuthBadMethodCallException()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage("Missing 'username' in options data");
        $this->table->find('auth');
    }

    /**
     * Test findAuth method.
     *
     * @expected
     */
    public function testFindAuth()
    {
        $user = $this->table
            ->find('auth', ['username' => 'not-exist@email.com'])
            ->toArray();
        $this->assertEmpty($user);

        $user = $this->table
            ->find('auth', ['username' => 'user-2@test.com'])
            ->first()
            ->toArray();

        $this->assertSame('00000000-0000-0000-0000-000000000002', Hash::get($user, 'id'));
        $this->assertSame('user-2', Hash::get($user, 'username'));
    }
}
