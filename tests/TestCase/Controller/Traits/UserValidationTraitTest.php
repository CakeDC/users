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

use Cake\Network\Request;
use Users\Test\BaseTraitTest;

class UserValidationTraitTest extends BaseTraitTest
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.users.users',
    ];

    /**
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->traitClassName = 'Users\Controller\Traits\UserValidationTrait';
        $this->traitMockMethods = ['dispatchEvent', 'isStopped', 'redirect', 'getUsersTable'];
        parent::setUp();
    }

    /**
     * test
     *
     * @return void
     */
    public function testValidateHappyToken()
    {
        $this->_mockFlash();
        $user = $this->table->findByToken('xxx')->first();
        $this->assertFalse($user->active);
        $this->Trait->Flash->expects($this->once())
                ->method('success')
                ->with('User account validated successfully');
        $this->Trait->validate('email', 'xxx');
        $user = $this->table->findById($user->id)->first();
        $this->assertTrue($user->active);
    }
}
