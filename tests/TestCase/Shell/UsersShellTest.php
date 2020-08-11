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

namespace CakeDC\Users\Test\TestCase\Shell;

use Cake\Console\ConsoleOutput;
use Cake\Console\Exception\StopException;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Shell\UsersShell;

class UsersShellTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.Users',
        'plugin.CakeDC/Users.SocialAccounts',
    ];

    /**
     * Set up
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->out = new ConsoleOutput();
        $this->io = $this->getMockBuilder('Cake\Console\ConsoleIo')->getMock();
        $this->Users = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');

        $this->Shell = $this->getMockBuilder('CakeDC\Users\Shell\UsersShell')
            ->setMethods(['in', 'out', '_stop', 'clear', '_usernameSeed', '_generateRandomPassword',
                '_generateRandomUsername', '_generatedHashedPassword', 'error', '_updateUser'])
            ->setConstructorArgs([$this->io])
            ->getMock();

        $this->Shell->Users = $this->getMockBuilder('CakeDC\Users\Model\Table\UsersTable')
            ->setMethods(['generateUniqueUsername', 'newEntity', 'save', 'updateAll'])
            ->getMock();

        $this->Shell->Command = $this->getMockBuilder('Cake\Shell\Task\CommandTask')
            ->setMethods(['in', '_stop', 'clear', 'out'])
            ->setConstructorArgs([$this->io])
            ->getMock();
    }

    /**
     * Tear Down
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->Shell);
    }

    /**
     * Add user test
     * Adding user with username, email, password and role
     *
     * @return void
     */
    public function testAddUser()
    {
        $user = [
            'username' => 'yeliparra',
            'password' => '123',
            'email' => 'yeli.parra@example.com',
            'active' => 1,
        ];
        $role = 'tester';

        $this->Shell->expects($this->never())
            ->method('_generateRandomUsername');

        $this->Shell->expects($this->never())
            ->method('_generateRandomPassword');

        $this->Shell->Users->expects($this->once())
            ->method('generateUniqueUsername')
            ->with($user['username'])
            ->will($this->returnValue($user['username']));

        $entityUser = $this->Users->newEntity($user);
        $entityUser->role = $role;

        $this->Shell->Users->expects($this->once())
            ->method('newEntity')
            ->with($user)
            ->will($this->returnValue($entityUser));

        $userSaved = $entityUser;
        $userSaved->id = 'my-id';

        $this->Shell->Users->expects($this->once())
            ->method('save')
            ->with($entityUser)
            ->will($this->returnValue($userSaved));

        $this->Shell->runCommand(['addUser', '--username=' . $user['username'], '--password=' . $user['password'], '--email=' . $user['email'], '--role=' . $role]);
    }

    /**
     * Add user test
     * Adding user passing no params
     *
     * @return void
     */
    public function testAddUserWithNoParams()
    {
        $user = [
            'username' => 'anakin',
            'password' => 'mypassword',
            'email' => 'anakin@example.com',
            'active' => 1,
        ];

        $this->Shell->Users->expects($this->once())
            ->method('generateUniqueUsername')
            ->with($user['username'])
            ->will($this->returnValue($user['username']));

        $this->Shell->expects($this->once())
            ->method('_generateRandomPassword')
            ->will($this->returnValue($user['password']));

        $this->Shell->expects($this->once())
            ->method('_generateRandomUsername')
            ->will($this->returnValue($user['username']));

        $entityUser = $this->Users->newEntity($user);
        $entityUser->role = 'user';

        $this->Shell->Users->expects($this->once())
            ->method('newEntity')
            ->with($user)
            ->will($this->returnValue($entityUser));

        $userSaved = $entityUser;
        $userSaved->id = 'my-id';

        $this->Shell->Users->expects($this->once())
            ->method('save')
            ->with($entityUser)
            ->will($this->returnValue($userSaved));

        //TODO: Add assertions with 'out'

        $this->Shell->runCommand(['addUser']);
    }

    /**
     * Add user test
     * Adding user with username, email, password and role
     *
     * @return void
     */
    public function testAddSuperuser()
    {
        $user = [
            'username' => 'yeliparra',
            'password' => '123',
            'email' => 'yeli.parra@example.com',
            'active' => 1,
        ];
        $role = 'tester';

        $this->Shell->expects($this->never())
            ->method('_generateRandomUsername');

        $this->Shell->expects($this->never())
            ->method('_generateRandomPassword');

        $this->Shell->Users->expects($this->once())
            ->method('generateUniqueUsername')
            ->with($user['username'])
            ->will($this->returnValue($user['username']));

        $entityUser = $this->Users->newEntity($user);
        $entityUser->role = $role;

        $this->Shell->Users->expects($this->once())
            ->method('newEntity')
            ->with($user)
            ->will($this->returnValue($entityUser));

        $userSaved = $entityUser;
        $userSaved->id = 'my-id';
        $userSaved->is_superuser = true;

        $this->Shell->Users->expects($this->once())
            ->method('save')
            ->with($entityUser)
            ->will($this->returnValue($userSaved));

        $this->Shell->runCommand(['addSuperuser', '--username=' . $user['username'], '--password=' . $user['password'], '--email=' . $user['email'], '--role=' . $role]);
    }

    /**
     * Add superadmin user
     *
     * @return void
     */
    public function testAddSuperuserWithNoParams()
    {
        $this->Shell->Users->expects($this->once())
            ->method('generateUniqueUsername')
            ->with('superadmin')
            ->will($this->returnValue('superadmin'));

        $this->Shell->expects($this->once())
            ->method('_generateRandomPassword')
            ->will($this->returnValue('password'));

        $user = [
            'username' => 'superadmin',
            'password' => 'password',
            'email' => 'superadmin@example.com',
            'active' => 1,
        ];
        $entityUser = $this->Users->newEntity($user);

        $this->Shell->Users->expects($this->once())
            ->method('newEntity')
            ->with($user)
            ->will($this->returnValue($entityUser));

        $userSaved = $entityUser;
        $userSaved->id = 'my-id';
        $userSaved->is_superuser = true;
        $userSaved->role = 'superuser';

        $this->Shell->Users->expects($this->once())
            ->method('save')
            ->with($entityUser)
            ->will($this->returnValue($userSaved));

        $this->Shell->runCommand(['addSuperuser']);
    }

    /**
     * Reset all passwords
     *
     * @return void
     */
    public function testResetAllPasswords()
    {
        $this->Shell->expects($this->once())
            ->method('_generatedHashedPassword')
            ->will($this->returnValue('hashedPasssword'));

        $this->Shell->Users->expects($this->once())
            ->method('updateAll')
            ->with(['password' => 'hashedPasssword'], ['id IS NOT NULL']);

        $this->Shell->runCommand(['resetAllPasswords', '123']);
    }

    /**
     * Reset all passwords
     */
    public function testResetAllPasswordsNoPassingParams()
    {
        $this->expectException(StopException::class);
        $this->expectExceptionMessage('Please enter a password.');
        $this->Shell->runCommand(['resetAllPasswords']);
    }

    /**
     * Reset password
     *
     * @return void
     */
    public function testResetPassword()
    {
        $user = $this->Users->newEmptyEntity();
        $user->username = 'user-1';
        $user->password = 'password';

        $this->Shell->expects($this->once())
            ->method('_updateUser')
            ->will($this->returnValue($user));

        $this->Shell->runCommand(['resetPassword', 'user-1', 'password']);
    }

    /**
     * Change role
     *
     * @return void
     */
    public function testChangeRole()
    {
        $this->Shell = new UsersShell($this->io);
        $this->Shell->Users = $this->Users;
        $user = $this->Users->get('00000000-0000-0000-0000-000000000001');
        $this->assertSame('admin', $user['role']);
        $this->Shell->runCommand(['changeRole', 'user-1', 'another-role']);
        $user = $this->Users->get('00000000-0000-0000-0000-000000000001');
        $this->assertSame('another-role', $user['role']);
    }

    /**
     * Activate user
     *
     * @return void
     */
    public function testActivateUser()
    {
        $this->Shell = new UsersShell($this->io);
        $this->Shell->Users = $this->Users;
        $user = $this->Users->get('00000000-0000-0000-0000-000000000001');
        $this->assertFalse($user['active']);
        $this->Shell->runCommand(['activateUser', 'user-1']);
        $user = $this->Users->get('00000000-0000-0000-0000-000000000001');
        $this->assertTrue($user['active']);
    }

    /**
     * Delete user
     *
     * @return void
     * @expected
     */
    public function testDeleteUser()
    {
        $this->Shell = new UsersShell($this->io);
        $this->Shell->Users = $this->Users;

        $this->assertNotEmpty($this->Users->findById('00000000-0000-0000-0000-000000000001')->first());
        $this->assertNotEmpty($this->Users->SocialAccounts->findByUserId('00000000-0000-0000-0000-000000000001')->toArray());
        $this->Shell->runCommand(['deleteUser', 'user-1']);
        $this->assertEmpty($this->Users->findById('00000000-0000-0000-0000-000000000001')->first());
        $this->assertEmpty($this->Users->SocialAccounts->findByUserId('00000000-0000-0000-0000-000000000001')->toArray());

        $this->assertNotEmpty($this->Users->findById('00000000-0000-0000-0000-000000000005')->first());
        $this->Shell->runCommand(['deleteUser', 'user-5']);
        $this->assertEmpty($this->Users->findById('00000000-0000-0000-0000-000000000005')->first());
    }

    /**
     * test
     *
     * @return void
     */
    public function testAddUserCustomRole()
    {
        $this->Shell = new UsersShell($this->io);
        $this->Shell->Users = $this->Users;
        $this->assertEmpty($this->Users->findByUsername('custom')->first());
        $this->Shell->runCommand([
            'addUser',
            '--username=custom',
            '--password=12345678',
            '--email=custom@example.com',
            '--role=custom',
        ]);
        $user = $this->Users->findByUsername('custom')->first();
        $this->assertSame('custom', $user['role']);
    }

    /**
     * test
     *
     * @return void
     */
    public function testAddUserDefaultRole()
    {
        $this->Shell = new UsersShell($this->io);
        $this->Shell->Users = $this->Users;
        $this->assertEmpty($this->Users->findByUsername('custom')->first());
        Configure::write('Users.Registration.defaultRole', false);
        $this->Shell->runCommand([
            'addUser',
            '--username=custom',
            '--password=12345678',
            '--email=custom@example.com',
        ]);
        $user = $this->Users->findByUsername('custom')->first();
        $this->assertSame('user', $user['role']);
    }

    /**
     * test
     *
     * @return void
     */
    public function testAddUserCustomDefaultRole()
    {
        $this->Shell = new UsersShell($this->io);
        $this->Shell->Users = $this->Users;
        $this->assertEmpty($this->Users->findByUsername('custom')->first());
        Configure::write('Users.Registration.defaultRole', 'emperor');
        $this->Shell->runCommand([
            'addUser',
            '--username=custom',
            '--password=12345678',
            '--email=custom@example.com',
        ]);
        $user = $this->Users->findByUsername('custom')->first();
        $this->assertSame('emperor', $user['role']);
    }
}
