<?php
declare(strict_types=1);

namespace CakeDC\Users\Command;

use Cake\Command\Command;
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
     * @return int|null The exit code or null for success
     */
    public function execute(): ?int
    {
        $this->createUser([
            'role' => Configure::read('Users.Registration.defaultRole') ?: 'user',
        ]);

        return static::CODE_SUCCESS;
    }

    /**
     * @inheritDoc
     */
    public static function defaultName(): string
    {
        return 'users add_user';
    }
}
