<?php
declare(strict_types=1);

namespace CakeDC\Users\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * CakeDC\Users\Command\UsersActivateUserCommand Test Case
 *
 * @uses \CakeDC\Users\Command\UsersActivateUserCommand
 */
class UsersActivateUserCommandTest extends TestCase
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
        $userIdTarget = '00000000-0000-0000-0000-000000000003';
        $this->assertTrue($UsersTable->exists(['id' => $userIdTarget, 'active' => 0]));
        $this->exec('cake_d_c/users.users activate_user user-3');
        $this->assertExitSuccess();
        $this->assertOutputRegExp('/^User was activated: user-3$/');
        //Target must have changed
        $this->assertTrue($UsersTable->exists(['id' => $userIdTarget, 'active' => 1]));
    }

    /**
     * Test execute method
     *
     * @return void
     * @uses \CakeDC\Users\Command\UsersChangeRoleCommand::execute()
     */
    public function testExecuteNoUsername(): void
    {
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $userIdTarget = '00000000-0000-0000-0000-000000000003';
        $this->assertTrue($UsersTable->exists(['id' => $userIdTarget, 'active' => 0]));
        $this->exec('cake_d_c/users.users activate_user');
        $this->assertExitError();
        $this->assertErrorContains('Please enter a username.');
        //Should not change
        $this->assertTrue($UsersTable->exists(['id' => $userIdTarget, 'active' => 0]));
    }
}
