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
    public function testValidateHappyEmail()
    {
        $this->_mockFlash();
        $user = $this->table->findByToken('token-3')->first();
        $this->assertFalse($user->active);
        $this->Trait->Flash->expects($this->once())
                ->method('success')
                ->with('User account validated successfully');
        $this->Trait->validate('email', 'token-3');
        $user = $this->table->findById($user->id)->first();
        $this->assertTrue($user->active);
    }

    /**
     * test
     *
     * @return void
     */
    public function testValidateHappyPassword()
    {
        $this->markTestIncomplete('test is progress now');
        $this->_mockFlash();
        $user = $this->table->findByToken('token-4')->first();
        $this->assertTrue($user->active);
        $oldPassword = $user->password;
        $this->Trait->Flash->expects($this->once())
                ->method('success')
                ->with('Reset password token was validated successfully');
        $this->Trait->validate('password', 'token-4');
        $user = $this->table->findById($user->id)->first();
        $this->assertTrue($user->active);
    }
}
