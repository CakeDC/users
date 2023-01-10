<?php
declare(strict_types=1);

namespace CakeDC\Users\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use CakeDC\Users\Command\Logic\UpdateUserTrait;

/**
 * UsersChangeApiToken command.
 */
class UsersChangeApiTokenCommand extends Command
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
            ->setDescription(__d('cake_d_c/users', 'Change the api token for an specific user'));
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
        $token = $args->getArgumentAt(1);
        if (empty($username)) {
            $io->abort(__d('cake_d_c/users', 'Please enter a username.'));
        }
        if (empty($token)) {
            $io->abort(__d('cake_d_c/users', 'Please enter a token.'));
        }
        $data = [
            'api_token' => $token,
        ];
        $savedUser = $this->_updateUser($io, $username, $data);
        if (!$savedUser) {
            $io->abort(__d('cake_d_c/users', 'User was not saved, check validation errors'));
        }
        /**
         * @var \CakeDC\Users\Model\Entity\User $savedUser
         */
        $io->out(__d('cake_d_c/users', 'Api token changed for user: {0}', $username));
        $io->out(__d('cake_d_c/users', 'New token: {0}', $savedUser->api_token));
    }

    /**
     * @inheritDoc
     */
    public static function defaultName(): string
    {
        return 'users change_api_token';
    }
}
