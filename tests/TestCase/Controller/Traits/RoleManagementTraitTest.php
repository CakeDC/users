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

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use CakeDC\Users\Exception\ConfigNotSetException;

class RoleManagementTraitTest extends BaseTraitTest
{

    use IntegrationTestTrait;


    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.Users',
    ];

    /**
     * setUp
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->traitClassName = 'CakeDC\Users\Controller\UsersController';
        $this->traitMockMethods = ['set', 'getUsersTable', 'redirect', 'validate'];
        parent::setUp();
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * test calling the action without authentication
     *
     * @return void
     */
    public function testCallingActionWithoutAuthentication()
    {
        $userId = '00000000-0000-0000-0000-000000000002';

        $this->_mockRequestPost();
        $this->Trait->getRequest()
            ->method('getData')
            ->will($this->returnValue([
                'role' => 'superuser',
            ]));
        $this->_mockFlash();
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('Login to perform this action');
        $this->Trait->changeRole($userId);
    }


    /**
     * test superuser cannot change roles with config set to false
     *
     * @return void
     */
    public function testSuperUserCannotChangeRoleWithDefaultConfig()
    {
        $superUser = $this->table->get('00000000-0000-0000-0000-000000000001');
        $userId = '00000000-0000-0000-0000-000000000002';
        $this->_mockRequestPost();
        $this->Trait->getRequest()
            ->method('getData')
            ->will($this->returnValue([
                'role' => 'superuser',
            ]));
        $this->_mockAuthLoggedIn($superUser->toArray());
        $this->_mockFlash();

        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('Changing role is not allowed');
        $this->Trait->changeRole($userId);
    }

    /**
     * test superuser can change role with updated config
     *
     * @return void
     */
    public function testSuperUserCanChangeRoleWithUpdatedConfig()
    {
        $superUser = $this->table->get('00000000-0000-0000-0000-000000000001');
        $userId = '00000000-0000-0000-0000-000000000002';

        //set the request
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['is', 'getData'])
            ->getMock();
        $this->Trait->setRequest($request);
        $this->Trait->getRequest()
            ->method('is')
            ->with(['post', 'put'])
            ->will($this->returnValue(true));

        $this->Trait->getRequest()
            ->method('getData')
            ->will($this->returnValue([
                'role' => 'superuser'
            ]));

        $this->_mockAuthLoggedIn($superUser->toArray());
        $this->_mockFlash();

        $this->Trait->Flash->expects($this->once())
            ->method('success')
            ->with('Role has been changed successfully');

        //update configuration
        Configure::write('Users.Superuser.allowedToChangeRoles', true);
        $this->Trait->changeRole($userId);

        $user = $this->table->get($userId);
        $this->assertEquals('superuser', $user->role);

    }


    /**
     * test superuser can change role with updated config
     *
     * @return void
     */
    public function testSuperUserCannotChangeRoleWithUpdatedConfigWhenInvalidRolePassed()
    {
        $superUser = $this->table->get('00000000-0000-0000-0000-000000000001');
        $userId = '00000000-0000-0000-0000-000000000002';

        //set the request
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['is', 'getData'])
            ->getMock();
        $this->Trait->setRequest($request);
        $this->Trait->getRequest()
            ->method('is')
            ->with(['post', 'put'])
            ->will($this->returnValue(true));

        $this->Trait->getRequest()
            ->method('getData')
            ->will($this->returnValue([
                'role' => 'random_role'
            ]));

        $this->_mockAuthLoggedIn($superUser->toArray());
        $this->_mockFlash();

        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('Role could not be changed');
        //update configuration
        Configure::write('Users.Superuser.allowedToChangeRoles', true);
        $this->Trait->changeRole($userId);
    }

    /**
     * test
     *
     * @return void
     */
    public function testErrorWhenNoConfigIsPresentForAvailableRoles()
    {
        $superUser = $this->table->get('00000000-0000-0000-0000-000000000001');
        $userId = '00000000-0000-0000-0000-000000000002';

        //set the request
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->setMethods(['is', 'getData'])
            ->getMock();
        $this->Trait->setRequest($request);
        $this->Trait->getRequest()
            ->method('is')
            ->with(['post', 'put'])
            ->will($this->returnValue(true));

        $this->Trait->getRequest()
            ->method('getData')
            ->will($this->returnValue([
                'role' => 'random_role'
            ]));

        $this->_mockAuthLoggedIn($superUser->toArray());
        $this->_mockFlash();

        $this->expectException(ConfigNotSetException::class);
        //update configuration
        Configure::write('Users.Superuser.allowedToChangeRoles', true);
        Configure::write('Users.AvailableRoles', []);
        $this->Trait->changeRole($userId);
    }
}
