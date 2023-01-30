<?php
declare(strict_types=1);

namespace CakeDC\Users\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * CakeDC\Users\Command\UsersChangeApiTokenCommand Test Case
 *
 * @uses \CakeDC\Users\Command\UsersChangeApiTokenCommand
 */
class UsersChangeApiTokenCommandTest extends TestCase
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
        $valueBefore = 'Lorem ipsum dolor sit amet';
        $userId001 = '00000000-0000-0000-0000-000000000001';
        $value001 = 'yyy';
        $value006 = '';
        $this->assertTrue($UsersTable->exists(['id' => $userIdTarget, 'api_token' => $valueBefore]));
        $this->assertTrue($UsersTable->exists(['id' => $userId001, 'api_token' => $value001]));
        $this->assertTrue($UsersTable->exists(['id' => $userId006, 'api_token' => $value006]));

        $this->exec('cake_d_c/users.users change_api_token user-4 test-execute-001');
        $this->assertExitSuccess();
        $this->assertOutputRegExp('/Api token changed for user: user-4\n/');
        $this->assertOutputRegExp('/New token: test-execute-001/');

        //$userId 1 and 2 should not have change the password
        $this->assertTrue($UsersTable->exists(['id' => $userId001, 'api_token' => $value001]));
        $this->assertTrue($UsersTable->exists(['id' => $userId006, 'api_token' => $value006]));
        //Target must have changed
        $this->assertTrue($UsersTable->exists(['id' => $userIdTarget, 'api_token' => 'test-execute-001']));
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
        $valueBefore = 'Lorem ipsum dolor sit amet';
        $userId001 = '00000000-0000-0000-0000-000000000001';
        $value001 = 'yyy';
        $value006 = '';
        $this->assertTrue($UsersTable->exists(['id' => $userIdTarget, 'api_token' => $valueBefore]));
        $this->assertTrue($UsersTable->exists(['id' => $userId001, 'api_token' => $value001]));
        $this->assertTrue($UsersTable->exists(['id' => $userId006, 'api_token' => $value006]));

        $this->exec($command);
        $this->assertExitError();
        $this->assertErrorContains($expectedMessage);

        //should not have change the password
        $this->assertTrue($UsersTable->exists(['id' => $userId001, 'api_token' => $value001]));
        $this->assertTrue($UsersTable->exists(['id' => $userId006, 'api_token' => $value006]));
        $this->assertTrue($UsersTable->exists(['id' => $userIdTarget, 'api_token' => $valueBefore]));
    }

    /**
     * @return array
     */
    public function dataProviderExecuteMissingInfo(): array
    {
        return [
            [
                'cake_d_c/users.users change_api_token user-4',
                'Please enter a token.',
            ],
            [
                'cake_d_c/users.users change_api_token',
                'Please enter a username.',
            ],
        ];
    }
}
