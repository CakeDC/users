<?php
declare(strict_types=1);

namespace CakeDC\Users\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use CakeDC\Users\Command\Logic\CreateUserTrait;

/**
 * UsersAddUser command.
 */
class UsersAddUserCommand extends Command
{
    use CreateUserTrait;

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null|void The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->_createUser($args, $io, [
            'role' => Configure::read('Users.Registration.defaultRole') ?: 'user',
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function defaultName(): string
    {
        return 'users add_user';
    }
}
