<?php
declare(strict_types=1);

namespace CakeDC\Users\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * CakeDC\Users\Command\UsersAddSuperuserCommand Test Case
 *
 * @uses \CakeDC\Users\Command\UsersAddSuperuserCommand
 */
class UsersAddSuperuserCommandTest extends TestCase
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
     * @uses \CakeDC\Users\Command\UsersAddUserCommand::execute()
     */
    public function testExecuteWithArgs(): void
    {
        $username = 'yeliparra.admin';
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $this->assertFalse($UsersTable->exists(['username' => $username]));
        $this->exec('cake_d_c/users.users add_superuser --username=yeliparra.admin --password=123456 --email=yeli.parra.testing01@testing.com --role=admin-tester');
        $this->_out->messages();
        $this->assertOutputRegExp('/Superuser added:/');
        $this->assertOutputRegExp('/Username: yeliparra.admin/');
        $this->assertOutputRegExp('/Email: yeli.parra.testing01@testing.com/');
        $this->assertOutputRegExp('/Role: admin-tester/');
        $this->assertOutputRegExp('/Password: 123456$/');
        /**
         * @var \CakeDC\Users\Model\Entity\User $user
         */
        $user = $UsersTable->find()->where(['username' => $username])->firstOrFail();
        $this->assertSame('yeli.parra.testing01@testing.com', $user->email);
        $this->assertSame('admin-tester', $user->role);
        $this->assertTrue($user->is_superuser);
        //Correct password?
        $passwordHasher = $user->getPasswordHasher();
        $this->assertTrue($passwordHasher->check('123456', $user->get('password')));
    }

    /**
     * Test execute method
     *
     * @return void
     * @uses \CakeDC\Users\Command\UsersAddUserCommand::execute()
     */
    public function testExecuteWithNoParams(): void
    {
        $this->exec('cake_d_c/users.users add_superuser');
        $this->assertOutputRegExp('/^Superuser added:/');
        $this->assertOutputRegExp('/Username: superadmin/');
        $this->assertOutputRegExp('/Email: superadmin@example.com/');
        $this->assertOutputRegExp('/Role: superuser/');
        $this->assertOutputRegExp('/Password: [a-z0-9]{32}$/');
    }
}
