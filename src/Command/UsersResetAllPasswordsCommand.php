<?php
declare(strict_types=1);

namespace CakeDC\Users\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use CakeDC\Users\Model\Entity\User;

/**
 * UsersResetAllPasswords command.
 */
class UsersResetAllPasswordsCommand extends Command
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
            ->setDescription(__d('cake_d_c/users', 'Reset the password for all users'));
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
        $password = $args->getArgumentAt(0);

        if (empty($password)) {
            $io->abort(__d('cake_d_c/users', 'Please enter a password.'));
        }
        $hashedPassword = $this->_generatedHashedPassword($password);
        $this->getTableLocator()->get('Users')->updateAll(['password' => $hashedPassword], ['id IS NOT NULL']);
        $io->out(__d('cake_d_c/users', 'Password changed for all users'));
        $io->out(__d('cake_d_c/users', 'New password: {0}', $password));
    }

    /**
     * @inheritDoc
     */
    public static function defaultName(): string
    {
        return 'users reset_all_passwords';
    }

    /**
     * Hash a password
     *
     * @param string $password password
     * @return string
     */
    protected function _generatedHashedPassword($password)
    {
        return (new User())->hashPassword($password);
    }
}
