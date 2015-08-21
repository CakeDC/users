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

namespace Users\Test\TestCase\Controller\Traits;

use Cake\ORM\TableRegistry;
use Users\Test\BaseTraitTest;

class RegisterTraitTest extends BaseTraitTest
{
    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        $this->traitClassName = 'Users\Controller\Traits\RegisterTrait';
        $this->traitMockMethods = ['validate', 'dispatchEvent', 'set'];
        parent::setUp();
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * test
     *
     * @return void
     */
    public function testValidateEmail()
    {
        $token = 'token';
        $this->Trait->expects($this->once())
                ->method('validate')
                ->with('email', $token);
        $this->Trait->validateEmail($token);
    }

    /**
     * test
     *
     * @return void
     */
    public function testRegisterHappy()
    {
        $this->_mockRequestPost();
        $this->_mockAuth();
        $this->_mockFlash();
        $this->_mockDispatchEvent();
        $this->Trait->register();
    }
}
