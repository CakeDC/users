<?php
declare(strict_types=1);

namespace CakeDC\Users\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * CakeDC\Users\Command\UsersChangeRoleCommand Test Case
 *
 * @uses \CakeDC\Users\Command\UsersChangeRoleCommand
 */
class UsersChangeRoleCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * @inheritDoc
     */
    protected array $fixtures = [
        'plugin.CakeDC/Users.Users',
        'plugin.CakeDC/Users.SocialAccounts',
    ];

    /**
     * Test execute method
     *
     * @return void
     * @uses \CakeDC\Users\Command\UsersChangeRoleCommand::execute()
     */
    public function testExecute(): void
    {
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $userIdTarget = '00000000-0000-0000-0000-000000000004';
        $userId006 = '00000000-0000-0000-0000-000000000006';
        $roleBefore = 'Lorem ipsum dolor sit amet';
        $userId001 = '00000000-0000-0000-0000-000000000001';
        $role001 = 'admin';
        $role006 = 'user';
        $this->assertTrue($UsersTable->exists(['id' => $userIdTarget, 'role' => $roleBefore]));
        $this->assertTrue($UsersTable->exists(['id' => $userId001, 'role' => $role001]));
        $this->assertTrue($UsersTable->exists(['id' => $userId006, 'role' => $role006]));

        $this->exec('cake_d_c/users.users change_role user-4 test-execute-001');
        $this->assertExitSuccess();
        $this->assertOutputRegExp('/Role changed for user: user-4/');
        $this->assertOutputRegExp('/New role: test-execute-001/');

        //$userId 1 and 2 should not have change the password
        $this->assertTrue($UsersTable->exists(['id' => $userId001, 'role' => $role001]));
        $this->assertTrue($UsersTable->exists(['id' => $userId006, 'role' => $role006]));
        //Target must have changed
        $this->assertTrue($UsersTable->exists(['id' => $userIdTarget, 'role' => 'test-execute-001']));
    }

    /**
     * Test execute method
     *
     * @param string $command
     * @param string $expectedMessage
     * @return void
     * @dataProvider dataProviderExecuteMissingInfo
     * @uses \CakeDC\Users\Command\UsersChangeRoleCommand::execute()
     */
    public function testExecuteMissingInfo(string $command, $expectedMessage): void
    {
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $userIdTarget = '00000000-0000-0000-0000-000000000004';
        $userId006 = '00000000-0000-0000-0000-000000000006';
        $roleBefore = 'Lorem ipsum dolor sit amet';
        $userId001 = '00000000-0000-0000-0000-000000000001';
        $role001 = 'admin';
        $role006 = 'user';
        $this->assertTrue($UsersTable->exists(['id' => $userIdTarget, 'role' => $roleBefore]));
        $this->assertTrue($UsersTable->exists(['id' => $userId001, 'role' => $role001]));
        $this->assertTrue($UsersTable->exists(['id' => $userId006, 'role' => $role006]));

        $this->exec($command);
        $this->assertExitError();
        $this->assertErrorContains($expectedMessage);

        //should not have change the password
        $this->assertTrue($UsersTable->exists(['id' => $userId001, 'role' => $role001]));
        $this->assertTrue($UsersTable->exists(['id' => $userId006, 'role' => $role006]));
        $this->assertTrue($UsersTable->exists(['id' => $userIdTarget, 'role' => $roleBefore]));
    }

    /**
     * @return array
     */
    public function dataProviderExecuteMissingInfo(): array
    {
        return [
            [
                'cake_d_c/users.users change_role user-4',
                'Please enter a role.',
            ],
            [
                'cake_d_c/users.users change_role',
                'Please enter a username.',
            ],
        ];
    }
}
