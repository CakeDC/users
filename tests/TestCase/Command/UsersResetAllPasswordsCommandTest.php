<?php
declare(strict_types=1);

namespace CakeDC\Users\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * CakeDC\Users\Command\UsersResetAllPasswordsCommand Test Case
 *
 * @uses \CakeDC\Users\Command\UsersResetAllPasswordsCommand
 */
class UsersResetAllPasswordsCommandTest extends TestCase
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
     * @uses \CakeDC\Users\Command\UsersResetAllPasswordsCommand::execute()
     */
    public function testExecute(): void
    {
        $this->exec('cake_d_c/users.users reset_all_passwords myCustomNewPass002');
        $this->assertExitSuccess();
        $this->assertOutputRegExp('/Password changed for all users\n/');
        $this->assertOutputRegExp('/New password: myCustomNewPass002/');

        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $users = $UsersTable->find()->all()->toArray();
        if (empty($users)) {
            throw new \UnexpectedValueException(__('Test expect to have users'));
        }
        foreach ($users as $user) {
            /**
             * @var \CakeDC\Users\Model\Entity\User $user
             */
            //Correct password?
            $passwordHasher = $user->getPasswordHasher();
            $this->assertTrue($passwordHasher->check('myCustomNewPass002', $user->get('password')));
        }
    }

    /**
     * Test execute method
     *
     * @return void
     * @uses \CakeDC\Users\Command\UsersResetAllPasswordsCommand::execute()
     */
    public function testExecuteNoPassword(): void
    {
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        /**
         * @var \CakeDC\Users\Model\Entity\User $user
         */
        $user = $UsersTable->find()->firstOrFail();
        $this->assertNotEmpty($user->password);
        $this->exec('cake_d_c/users.users reset_all_passwords');
        $this->assertExitError();
        $this->assertErrorContains('Please enter a password.');
        $this->assertOutputNotContains('Password changed');
        /**
         * @var \CakeDC\Users\Model\Entity\User $userAfter
         */
        $userAfter = $UsersTable->get($user->id);
        $this->assertSame($user->password, $userAfter->password);
    }
}
