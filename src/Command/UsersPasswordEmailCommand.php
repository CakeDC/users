<?php
declare(strict_types=1);

namespace CakeDC\Users\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;

/**
 * UsersPasswordEmail command.
 */
class UsersPasswordEmailCommand extends Command
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
            ->setDescription(__d('cake_d_c/users', 'Reset the password via email'));
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
        $reference = $args->getArgumentAt(0);
        if (empty($reference)) {
            $io->abort(__d('cake_d_c/users', 'Please enter a username or email.'));
        }
        /**
         * @var \CakeDC\Users\Model\Table\UsersTable $UsersTable
         */
        $UsersTable = $this->getTableLocator()->get('Users');
        $resetUser = $UsersTable->resetToken($reference, [
            'expiration' => Configure::read('Users.Token.expiration'),
            'checkActive' => false,
            'sendEmail' => true,
        ]);
        if ($resetUser) {
            $msg = __d(
                'cake_d_c/users',
                'Please ask the user to check the email to continue with password reset process'
            );
            $io->out($msg);
        } else {
            $msg = __d(
                'cake_d_c/users',
                'The password token could not be generated. Please try again'
            );
            $io->abort($msg);
        }
    }

    /**
     * @inheritDoc
     */
    public static function defaultName(): string
    {
        return 'users password_email';
    }
}
