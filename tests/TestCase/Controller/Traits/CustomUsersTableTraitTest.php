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

namespace CakeDC\Users\Test\TestCase\Controller\Traits;

use Cake\ORM\Table;
use Cake\TestSuite\TestCase;

class CustomUsersTableTraitTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->controller = $this->getMockBuilder('Cake\Controller\Controller')
                ->setMethods(['header', 'redirect', 'render', '_stop'])
                ->getMock();
        $this->controller->Trait = $this->getMockForTrait('CakeDC\Users\Controller\Traits\CustomUsersTableTrait');
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testGetUsersTable()
    {
        $table = $this->controller->Trait->getUsersTable();
        $this->assertEquals('CakeDC/Users.Users', $table->getRegistryAlias());
        $newTable = new Table();
        $this->controller->Trait->setUsersTable($newTable);
        $this->assertSame($newTable, $this->controller->Trait->getUsersTable());
    }
}
