<?php
declare(strict_types=1);

namespace CakeDC\Users\Command;

use Cake\Command\Command;
use Cake\Console\ConsoleOptionParser;
use CakeDC\Users\Command\Logic\UpdateUserTrait;

/**
 * UsersResetPassword command.
 */
class UsersResetPasswordCommand extends Command
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
            ->setDescription(__d('cake_d_c/users', 'Reset the password for a specific user'));
    }

    /**
     * Implement this method with your command's logic.
     *
     * @return int|null The exit code or null for success
     */
    public function execute(): ?int
    {
        $username = $this->args->getArgumentAt(0);
        $password = $this->args->getArgumentAt(1);
        if (empty($username)) {
            $this->io->abort(__d('cake_d_c/users', 'Please enter a username.'));
        }
        if (empty($password)) {
            $this->io->abort(__d('cake_d_c/users', 'Please enter a password.'));
        }
        $data = [
            'password' => $password,
        ];
        $this->updateUser($username, $data);
        $this->io->out(__d('cake_d_c/users', 'Password changed for user: {0}', $username));
        $this->io->out(__d('cake_d_c/users', 'New password: {0}', $password));

        return static::CODE_SUCCESS;
    }

    /**
     * @inheritDoc
     */
    public static function defaultName(): string
    {
        return 'users reset_password';
    }
}
