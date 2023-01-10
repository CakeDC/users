<?php
declare(strict_types=1);

namespace CakeDC\Users\Test\TestCase\Command;

use App\Command\UsersPasswordEmailCommand;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Command\UsersPasswordEmailCommand Test Case
 *
 * @uses \App\Command\UsersPasswordEmailCommand
 */
class UsersPasswordEmailCommandTest extends TestCase
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
        /**
         * @var \CakeDC\Users\Model\Table\UsersTable $UsersTable
         */
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $userIdTarget = '00000000-0000-0000-0000-000000000003';
        $user = $UsersTable->get($userIdTarget);
        $this->assertSame('token-3', $user->token);

        $this->exec('cake_d_c/users.users password_email user-3');
        $this->assertExitSuccess();
        $this->assertOutputRegExp('/^Please ask the user to check the email to continue with password reset process$/');
        //Target must have changed
        $userAfter = $UsersTable->get($userIdTarget);
        $this->assertNotEmpty($userAfter->token);
        $this->assertNotEquals($user->token, $userAfter->token);
        $this->assertNotEmpty($userAfter->token_expires);
        $this->assertNotEquals($user->token_expires, $userAfter->token_expires);
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
        $this->exec('cake_d_c/users.users password_email');
        $this->assertExitError();
        $this->assertErrorContains('Please enter a username or email.');
        //Should not change
        $user = $UsersTable->get($userIdTarget);
        $this->assertSame('token-3', $user->token);
    }
}
