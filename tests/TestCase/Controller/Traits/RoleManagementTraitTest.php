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

use Cake\Auth\PasswordHasherFactory;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class RoleManagementTraitTest extends BaseTraitTest
{

    use IntegrationTestTrait;

    /**
     * @var \CakeDC\Users\Controller\UsersController
     */
    public $Trait;

    /**
     * setUp
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->traitClassName = 'CakeDC\Users\Controller\UsersController';
        $this->traitMockMethods = ['set', 'redirect', 'validate', 'log', 'dispatchEvent'];
        $this->mockDefaultEmail = true;
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
     * test dont allow super to change role
     *
     * @return void
     */
    public function testSuperUserChangeRoleWithDefaultConfig()
    {
        $superUser = $this->table->get('00000000-0000-0000-0000-000000000001'); //super user with role admin
        $this->enableSecurityToken();
        $this->session([
            'Auth' => $superUser
        ]);
        $this->put('/users/changeRole/00000000-0000-0000-0000-000000000001', ['role' => 'superuser']);//change role to superuser
        $superUser = $this->table->get('00000000-0000-0000-0000-000000000001');
        $this->assertEquals('admin', $superUser->role);
    }

    /**
     * test allow super user to change role
     *
     * @return void
     */
    public function testSuperUserChangeRoleWithUpdatedConfig()
    {
        Configure::write('Users.Superuser.allowedToChangeRoles', true);
        $superUser = $this->table->get('00000000-0000-0000-0000-000000000001');//super user with role admin
        $this->enableSecurityToken();
        $this->session([
            'Auth' => $superUser
        ]);
        $this->put('/users/changeRole/00000000-0000-0000-0000-000000000001', ['role' => 'superuser']);//change role to superuser
        $superUser = $this->table->get('00000000-0000-0000-0000-000000000001');
        $this->assertEquals('superuser', $superUser->role);
    }


}
