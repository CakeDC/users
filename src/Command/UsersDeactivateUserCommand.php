<?php
declare(strict_types=1);

namespace CakeDC\Users\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use CakeDC\Users\Command\Logic\ChangeUserActiveTrait;

/**
 * UsersDeactivateUser command.
 */
class UsersDeactivateUserCommand extends Command
{
    use ChangeUserActiveTrait;

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
            ->setDescription(__d('cake_d_c/users', 'Deactivate a specific user'));
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null|void The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $user = $this->_changeUserActive($args, $io, false);
        $io->out(__d('cake_d_c/users', 'User was de-activated: {0}', $user->username));
    }

    /**
     * @inheritDoc
     */
    public static function defaultName(): string
    {
        return 'users deactivate_user';
    }
}
