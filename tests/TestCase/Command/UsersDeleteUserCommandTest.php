<?php
declare(strict_types=1);

namespace CakeDC\Users\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Command\UsersDeleteUserCommand Test Case
 *
 * @uses \App\Command\UsersDeleteUserCommand
 */
class UsersDeleteUserCommandTest extends TestCase
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
     * @uses \App\Command\UsersChangeRoleCommand::execute()
     */
    public function testExecute(): void
    {
        /**
         * @var \CakeDC\Users\Model\Table\UsersTable $UsersTable
         */
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $userIdTarget = '00000000-0000-0000-0000-000000000003';
        $this->assertTrue($UsersTable->exists(['id' => $userIdTarget]));
        $this->assertTrue($UsersTable->SocialAccounts->exists(['user_id' => $userIdTarget]));
        $this->exec('cake_d_c/users.users delete_user user-3');
        $this->assertExitSuccess();
        $this->assertOutputRegExp('/^The user user-3 was deleted successfully$/');
        //Target must have changed
        $this->assertFalse($UsersTable->exists(['id' => $userIdTarget]));
        $this->assertFalse($UsersTable->SocialAccounts->exists(['user_id' => $userIdTarget]));
    }

    /**
     * Test execute method
     *
     * @return void
     * @uses \App\Command\UsersChangeRoleCommand::execute()
     */
    public function testExecuteNoUsername(): void
    {
        /**
         * @var \CakeDC\Users\Model\Table\UsersTable $UsersTable
         */
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $userIdTarget = '00000000-0000-0000-0000-000000000003';
        $this->assertTrue($UsersTable->exists(['id' => $userIdTarget]));
        $this->assertTrue($UsersTable->SocialAccounts->exists(['user_id' => $userIdTarget]));
        $this->exec('cake_d_c/users.users delete_user');
        $this->assertExitError();
        $this->assertErrorContains('Please enter a username.');
        //Target must have changed
        $this->assertTrue($UsersTable->exists(['id' => $userIdTarget]));
        $this->assertTrue($UsersTable->SocialAccounts->exists(['user_id' => $userIdTarget]));
    }
}
