<?php
declare(strict_types=1);

namespace CakeDC\Users\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Core\Configure;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * CakeDC\Users\Command\UsersAddUserCommand Test Case
 *
 * @uses \CakeDC\Users\Command\UsersAddUserCommand
 */
class UsersAddUserCommandTest extends TestCase
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
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $this->assertFalse($UsersTable->exists(['username' => 'yeliparra']));
        $this->exec('cake_d_c/users.users add_user --username=yeliparra --password=123 --email=yeli.parra@test.com --role=tester');
        $this->assertOutputRegExp('/^User added:/');
        $this->assertOutputRegExp('/Username: yeliparra/');
        $this->assertOutputRegExp('/Email: yeli.parra@test.com/');
        $this->assertOutputRegExp('/Role: tester/');
        $this->assertOutputRegExp('/Password: 123$/');
        /**
         * @var \CakeDC\Users\Model\Entity\User $user
         */
        $user = $UsersTable->find()->where(['username' => 'yeliparra'])->firstOrFail();
        $this->assertSame('yeli.parra@test.com', $user->email);
        $this->assertSame('tester', $user->role);
        $this->assertFalse($user->is_superuser);
        //Correct password?
        $passwordHasher = $user->getPasswordHasher();
        $this->assertTrue($passwordHasher->check('123', $user->get('password')));
    }

    /**
     * Test execute method
     *
     * @return void
     * @uses \CakeDC\Users\Command\UsersAddUserCommand::execute()
     */
    public function testExecuteWithNoParams(): void
    {
        $this->exec('cake_d_c/users.users add_user');
        $username = '(aayla|admiral|anakin|chewbacca|darthvader|hansolo|luke|obiwan|leia|r2d2)';
        $this->assertOutputRegExp('/^User added:/');
        $this->assertOutputRegExp('/Username: ' . $username . '/');
        $this->assertOutputRegExp('/Email: ' . $username . '@example.com/');
        $this->assertOutputRegExp('/Role: user/');
        $this->assertOutputRegExp('/Password: [a-z0-9]{32}$/');
    }

    /**
     * Test execute method
     *
     * @return void
     */
    public function testExecuteCustomDefaultRole()
    {
        EventManager::instance()->on('Console.buildCommands', function () {
            Configure::write('Users.Registration.defaultRole', 'emperor');
        });
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $username = 'custom.user';
        $this->assertFalse($UsersTable->exists(['username' => $username]));

        $this->exec('cake_d_c/users.users add_user --username=custom.user --password=12345 --email=custom+user@example.com');
        $this->assertOutputRegExp('/^User added:/');
        $this->assertOutputRegExp('/Username: custom.user/');
        $this->assertOutputRegExp('/Email: custom\+user@example.com/');
        $this->assertOutputRegExp('/Role: emperor/');
        $this->assertOutputRegExp('/Password: 12345$/');

        /**
         * @var \CakeDC\Users\Model\Entity\User $user
         */
        $user = $UsersTable->find()->where(['username' => $username])->firstOrFail();
        $this->assertSame('custom+user@example.com', $user->email);
        $this->assertSame('emperor', $user->role);
        $this->assertFalse($user->is_superuser);
        //Correct password?
        $passwordHasher = $user->getPasswordHasher();
        $this->assertTrue($passwordHasher->check('12345', $user->get('password')));
    }

    /**
     * Test execute method
     *
     * @return void
     */
    public function testExecuteDefaultRole()
    {
        EventManager::instance()->on('Console.buildCommands', function () {
            Configure::write('Users.Registration.defaultRole', false);
        });
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $username = 'custom.user';
        $this->assertFalse($UsersTable->exists(['username' => $username]));

        $this->exec('cake_d_c/users.users add_user --username=custom.user --password=12345 --email=custom+user@example.com');
        $this->assertOutputRegExp('/^User added:/');
        $this->assertOutputRegExp('/Username: custom.user/');
        $this->assertOutputRegExp('/Email: custom\+user@example.com/');
        $this->assertOutputRegExp('/Role: user/');
        $this->assertOutputRegExp('/Password: 12345$/');

        /**
         * @var \CakeDC\Users\Model\Entity\User $user
         */
        $user = $UsersTable->find()->where(['username' => $username])->firstOrFail();
        $this->assertSame('custom+user@example.com', $user->email);
        $this->assertSame('user', $user->role);
        $this->assertFalse($user->is_superuser);
        //Correct password?
        $passwordHasher = $user->getPasswordHasher();
        $this->assertTrue($passwordHasher->check('12345', $user->get('password')));
    }
}
