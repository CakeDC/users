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

namespace CakeDC\Users\Test\TestCase\Shell;

use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOutput;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class UsersShellTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.users',
    ];

    /**
     * Set up
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->out = new ConsoleOutput();
        $this->io = new ConsoleIo($this->out);
        $this->Users = TableRegistry::get('CakeDC/Users.Users');

        $this->Shell = $this->getMockBuilder('CakeDC\Users\Shell\UsersShell')
            ->setMethods(['in', 'out', '_stop', 'clear', '_usernameSeed', '_generateRandomPassword',
                '_generateRandomUsername', '_generatedHashedPassword', 'error'])
            ->setConstructorArgs([$this->io])
            ->getMock();

        $this->Shell->Users = $this->getMockBuilder('CakeDC\Users\Model\UsersTable')
            ->setMethods(['generateUniqueUsername', 'newEntity', 'save', 'updateAll'])
            ->getMock();

        $this->Shell->Command = $this->getMock(
            'Cake\Shell\Task\CommandTask',
            ['in', '_stop', 'clear', 'out'],
            [$this->io]
        );
    }

    /**
     * Tear Down
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        unset($this->Shell);
    }

    /**
     * Add user test
     * Adding user with username, email and password
     *
     * @return void
     */
    public function testAddUser()
    {
        $user = [
            'username' => 'yeliparra',
            'password' => '123',
            'email' => 'yeli.parra@gmail.com',
            'active' => 1,
        ];

        $this->Shell->expects($this->never())
            ->method('_generateRandomUsername');

        $this->Shell->expects($this->never())
            ->method('_generateRandomPassword');

        $this->Shell->Users->expects($this->once())
            ->method('generateUniqueUsername')
            ->with($user['username'])
            ->will($this->returnValue($user['username']));

        $entityUser = $this->Users->newEntity($user);

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

        $this->Shell->runCommand(['addUser', '--username=' . $user['username'], '--password=' . $user['password'], '--email=' . $user['email']]);
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
     * Add superadmin user
     *
     * @return void
     */
    public function testAddSuperuser()
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
     *
     * @return void
     */
    public function testResetAllPasswordsNoPassingParams()
    {
        $this->Shell->expects($this->once())
            ->method('error')
            ->with('Please enter a password.');

        $this->Shell->runCommand(['resetAllPasswords']);
    }
}
