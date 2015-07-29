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
use Cake\TestSuite\TestCase;

class UserValidationTraitTest extends TestCase
{
    /**
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $request = new Request();
        $this->Trait = $this->getMockBuilder('Users\Controller\Traits\UserValidationTrait')
            ->setMethods(['dispatchEvent', 'isStopped', 'redirect', 'getUsersTable'])
            ->getMockForTrait();
        $this->Trait->request = $request;
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
    public function testValidateHappyToken()
    {
        $usersTableMock = $this->getMockBuilder('Users\Model\Table\UsersTable')
                ->setMethods(['validate'])
                ->disableOriginalConstructor()
                ->getMock();
        $usersTableMock->expects($this->once())
                ->method('validate')
                ->with('token', 'activateUser')
                ->will($this->returnValue(true));
        $this->Trait->expects($this->once())
                ->method('getUsersTable')
                ->will($this->returnValue($usersTableMock));
        $this->Trait->Flash = $this->getMockBuilder('Cake\Controller\Component\FlashComponent')
            ->setMethods(['success'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->Trait->Flash->expects($this->once())
                ->method('success')
                ->with('User account validated successfully');
        $this->Trait->validate('email', 'token');
    }
}
