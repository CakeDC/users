<?php
declare(strict_types=1);

namespace CakeDC\Users\Test\TestCase\Command;

use App\Command\UsersDeactivateUserCommand;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Command\UsersDeactivateUserCommand Test Case
 *
 * @uses \App\Command\UsersDeactivateUserCommand
 */
class UsersDeactivateUserCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * @inheritdoc
     */
    protected array $fixtures = [
        'plugin.CakeDC/Users.Users',
        'plugin.CakeDC/Users.SocialAccounts',
    ];

    /**
     * Test execute method
     *
     * @return void
     * @uses \App\Command\UsersChangeRoleCommand::execute()
     */
    public function testExecute(): void
    {
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $userIdTarget = '00000000-0000-0000-0000-000000000002';
        $this->assertTrue($UsersTable->exists(['id' => $userIdTarget, 'active' => 1]));
        $this->exec('cake_d_c/users.users deactivate_user user-2');
        $this->assertExitSuccess();
        $this->assertOutputRegExp('/^User was de-activated: user-2$/');
        //Target must have changed
        $this->assertTrue($UsersTable->exists(['id' => $userIdTarget, 'active' => 0]));
    }

    /**
     * Test execute method
     *
     * @return void
     * @uses \App\Command\UsersChangeRoleCommand::execute()
     */
    public function testExecuteNoUsername(): void
    {
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $userIdTarget = '00000000-0000-0000-0000-000000000002';
        $this->assertTrue($UsersTable->exists(['id' => $userIdTarget, 'active' => 1]));
        $this->exec('cake_d_c/users.users deactivate_user');
        $this->assertExitError();
        $this->assertErrorContains('Please enter a username.');
        //Should not change
        $this->assertTrue($UsersTable->exists(['id' => $userIdTarget, 'active' => 1]));
    }
}
