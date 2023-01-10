<?php
declare(strict_types=1);

namespace CakeDC\Users\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use CakeDC\Users\Command\Logic\CreateUserTrait;

/**
 * UsersAddSuperuser command.
 */
class UsersAddSuperuserCommand extends Command
{
    use CreateUserTrait;

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|void|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->_createUser($args, $io, [
            'username' => 'superadmin',
            'role' => 'superuser',
            'is_superuser' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function defaultName(): string
    {
        return 'users add_superuser';
    }
}
