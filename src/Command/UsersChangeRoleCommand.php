<?php
declare(strict_types=1);

namespace CakeDC\Users\Command;

use Cake\Command\Command;
use Cake\Console\ConsoleOptionParser;
use CakeDC\Users\Command\Logic\UpdateUserTrait;

/**
 * UsersChangeRole command.
 */
class UsersChangeRoleCommand extends Command
{
    use UpdateUserTrait;

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
            ->setDescription(__d('cake_d_c/users', 'Change the role for a specific user'));
    }

    /**
     * Implement this method with your command's logic.
     *
     * @return int|null The exit code or null for success
     */
    public function execute(): ?int
    {
        $username = $this->args->getArgumentAt(0);
        $role = $this->args->getArgumentAt(1);
        if (empty($username)) {
            $this->io->abort(__d('cake_d_c/users', 'Please enter a username.'));
        }
        if (empty($role)) {
            $this->io->abort(__d('cake_d_c/users', 'Please enter a role.'));
        }
        $data = [
            'role' => $role,
        ];
        $savedUser = $this->updateUser($username, $data);
        $this->io->out(__d('cake_d_c/users', 'Role changed for user: {0}', $username));
        $this->io->out(__d('cake_d_c/users', 'New role: {0}', $savedUser->role));

        return static::CODE_SUCCESS;
    }

    /**
     * @inheritDoc
     */
    public static function defaultName(): string
    {
        return 'users change_role';
    }
}
