<?php
declare(strict_types=1);

namespace CakeDC\Users\Command;

use Cake\Command\Command;
use CakeDC\Users\Command\Logic\CreateUserTrait;
use CakeDC\Users\Model\Table\UsersTable;

/**
 * UsersAddSuperuser command.
 */
class UsersAddSuperuserCommand extends Command
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
            'username' => 'superadmin',
            'role' => UsersTable::ROLE_ADMIN,
            'is_superuser' => true,
        ]);

        return static::CODE_SUCCESS;
    }

    /**
     * @inheritDoc
     */
    public static function defaultName(): string
    {
        return 'users add_superuser';
    }
}
