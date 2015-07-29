<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (+1 702 425 5085) (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (+1 702 425 5085) (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Users\Test\TestCase\Controller\Traits;

use Cake\TestSuite\TestCase;

class PasswordManagementTraitTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->controller = $this->getMock(
            'Cake\Controller\Controller',
            ['header', 'redirect', 'render', '_stop']
        );
        $this->controller->Trait = $this->getMockForTrait('Users\Controller\Traits\PasswordManagementTrait');
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testChangePassword()
    {
        $this->markTestIncomplete();
    }

    public function testResetPassword()
    {
        $this->markTestIncomplete();
    }

    public function testRequestResetPassword()
    {
        $this->markTestIncomplete();
    }
}
