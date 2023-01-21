<?php
declare(strict_types=1);

namespace CakeDC\Users\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * CakeDC\Users\Command\UsersResetPasswordCommand Test Case
 *
 * @uses \CakeDC\Users\Command\UsersResetPasswordCommand
 */
class UsersResetPasswordCommandTest extends TestCase
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
     * @uses \CakeDC\Users\Command\UsersResetPasswordCommand::execute()
     */
    public function testExecute(): void
    {
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $userId004 = '00000000-0000-0000-0000-000000000004';
        $userId006 = '00000000-0000-0000-0000-000000000006';
        $passwordBefore = '$2y$10$Nvu7ipP.z8tiIl75OdUvt.86vuG6iKMoHIOc7O7mboFI85hSyTEde';
        $userId001 = '00000000-0000-0000-0000-000000000001';
        $password001 = '12345';
        $password006 = '$2y$10$IPPgJNSfvATsMBLbv/2r8OtpyTBibyM1g5GDxD4PivW9qBRwRkRbC';
        $this->assertTrue($UsersTable->exists(['id' => $userId004, 'password' => $passwordBefore]));
        $this->assertTrue($UsersTable->exists(['id' => $userId001, 'password' => $password001]));
        $this->assertTrue($UsersTable->exists(['id' => $userId006, 'password' => $password006]));

        $this->exec('cake_d_c/users.users reset_password user-4 newPassTestOne234');
        $this->assertExitSuccess();
        $this->assertOutputRegExp('/Password changed for user: user-4\n/');
        $this->assertOutputRegExp('/New password: newPassTestOne234/');

        //$userId 1 and 2 should not have change the password
        $this->assertTrue($UsersTable->exists(['id' => $userId001, 'password' => $password001]));
        $this->assertTrue($UsersTable->exists(['id' => $userId006, 'password' => $password006]));
        /**
         * @var \CakeDC\Users\Model\Entity\User $user
         */
        $user = $UsersTable->get($userId004);
        $this->assertNotEquals($passwordBefore, $user->password);
        //Correct password?
        $passwordHasher = $user->getPasswordHasher();
        $this->assertTrue($passwordHasher->check('newPassTestOne234', $user->get('password')));
    }

    /**
     * Test execute method
     *
     * @return void
     * @uses \CakeDC\Users\Command\UsersResetPasswordCommand::execute()
     */
    public function testExecuteNotPassword(): void
    {
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $userId004 = '00000000-0000-0000-0000-000000000004';
        $passwordBefore = '$2y$10$Nvu7ipP.z8tiIl75OdUvt.86vuG6iKMoHIOc7O7mboFI85hSyTEde';
        $this->assertTrue($UsersTable->exists(['id' => $userId004, 'password' => $passwordBefore]));

        $this->exec('cake_d_c/users.users reset_password user-4');
        $this->assertExitError();
        $this->assertErrorContains('Please enter a password.');

        //Password not changed
        $this->assertTrue($UsersTable->exists(['id' => $userId004, 'password' => $passwordBefore]));
    }

    /**
     * Test execute method
     *
     * @return void
     * @uses \CakeDC\Users\Command\UsersResetPasswordCommand::execute()
     */
    public function testExecuteNotUserName(): void
    {
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $userId004 = '00000000-0000-0000-0000-000000000004';
        $passwordBefore = '$2y$10$Nvu7ipP.z8tiIl75OdUvt.86vuG6iKMoHIOc7O7mboFI85hSyTEde';
        $this->assertTrue($UsersTable->exists(['id' => $userId004, 'password' => $passwordBefore]));

        $this->exec('cake_d_c/users.users reset_password');
        $this->assertExitError();
        $this->assertErrorContains('Please enter a username.');

        //Password not changed
        $this->assertTrue($UsersTable->exists(['id' => $userId004, 'password' => $passwordBefore]));
    }
}
