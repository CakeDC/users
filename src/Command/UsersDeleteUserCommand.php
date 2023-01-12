<?php
declare(strict_types=1);

namespace CakeDC\Users\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * UsersDeleteUser command.
 */
class UsersDeleteUserCommand extends Command
{
    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/4/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        return $parser
            ->setDescription(__d('cake_d_c/users', 'Delete an specific user'));
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|void|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $username = $args->getArgumentAt(0);
        if (empty($username)) {
            $io->abort(__d('cake_d_c/users', 'Please enter a username.'));
        }
        /**
         * @var \CakeDC\Users\Model\Table\UsersTable $UsersTable
         */
        $UsersTable = $this->getTableLocator()->get('Users');
        /**
         * @var \Cake\Datasource\EntityInterface $user
         */
        $user = $UsersTable->find()->where(['username' => $username])->firstOrFail();
        if (isset($UsersTable->SocialAccounts)) {
            $UsersTable->SocialAccounts->deleteAll(['user_id' => $user->id]);
        }
        $deleteUser = $UsersTable->delete($user);
        if (!$deleteUser) {
            $io->abort(__d('cake_d_c/users', 'The user {0} was not deleted. Please try again', $username));
        }
        $io->out(__d('cake_d_c/users', 'The user {0} was deleted successfully', $username));
    }

    /**
     * @inheritDoc
     */
    public static function defaultName(): string
    {
        return 'users delete_user';
    }
}
