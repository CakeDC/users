<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Controller\Traits;

use Cake\ORM\Table;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Controller\Traits\CustomUsersTableTrait;

class CustomUsersTableTraitTest extends TestCase
{
    /**
     * Instance of the trait under test (avoid dynamic properties on mocks)
     */
    protected $Trait;

    public function setUp(): void
    {
        parent::setUp();
        $this->Trait = new class {
            use CustomUsersTableTrait;
        };
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testGetUsersTable()
    {
        $table = $this->Trait->getUsersTable();
        $this->assertEquals('CakeDC/Users.Users', $table->getRegistryAlias());
        $newTable = new Table();
        $this->Trait->setUsersTable($newTable);
        $this->assertSame($newTable, $this->Trait->getUsersTable());
    }
}
