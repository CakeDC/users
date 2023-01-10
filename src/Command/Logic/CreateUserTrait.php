<?php

namespace CakeDC\Users\Command\Logic;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Utility\Text;

trait CreateUserTrait
{
    use LocatorAwareTrait;

    /**
     * Work as a seed for username generator
     *
     * @var array
     */
    protected array $_usernameSeed = [
        'aayla', 'admiral', 'anakin', 'chewbacca',
        'darthvader', 'hansolo', 'luke', 'obiwan', 'leia', 'r2d2',
    ];

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
            ->addOptions([
                'username' => ['short' => 'u', 'help' => 'The username for the new user'],
                'password' => ['short' => 'p', 'help' => 'The password for the new user'],
                'email' => ['short' => 'e', 'help' => 'The email for the new user'],
                'role' => ['short' => 'r', 'help' => 'The role for the new user'],
            ]);
    }

    /**
     * Create a new user or superuser
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @param array $template template with deafault user values
     * @return void
     */
    protected function _createUser(Arguments $args, ConsoleIo $io, $template)
    {
        /**
         * @var \CakeDC\Users\Model\Table\UsersTable $UsersTable
         */
        $UsersTable = $this->getTableLocator()->get('Users');
        $username = $args->getOption('username');
        $password = $args->getOption('password');
        $email = $args->getOption('email');
        $role = $args->getOption('role');
        if (empty($username)) {
            $username = empty($template['username'])
                ? $this->_generateRandomUsername()
                : $template['username'];
        }

        $password = $password ?: $this->_generateRandomPassword();
        $email = $email ?: $username . '@example.com';
        $role = $role ?: $template['role'];

        $user = [
            'username' => $UsersTable->generateUniqueUsername($username),
            'email' => $email,
            'password' => $password,
            'active' => 1,
        ];

        $userEntity = $UsersTable->newEntity($user);
        $userEntity->is_superuser = empty($template['is_superuser']) ?
            false : $template['is_superuser'];
        $userEntity->role = $role;
        $savedUser = $UsersTable->save($userEntity);

        if (is_object($savedUser)) {
            if ($savedUser->is_superuser) {
                $io->out(__d('cake_d_c/users', 'Superuser added:'));
            } else {
                $io->out(__d('cake_d_c/users', 'User added:'));
            }
            $io->out(__d('cake_d_c/users', 'Id: {0}', $savedUser->id));
            $io->out(__d('cake_d_c/users', 'Username: {0}', $savedUser->username));
            $io->out(__d('cake_d_c/users', 'Email: {0}', $savedUser->email));
            $io->out(__d('cake_d_c/users', 'Role: {0}', $savedUser->role));
            $io->out(__d('cake_d_c/users', 'Password: {0}', $password));
        } else {
            $io->out(__d('cake_d_c/users', 'User could not be added:'));

            collection($userEntity->getErrors())->each(function ($error, $field) use ($io) {
                $io->out(__d('cake_d_c/users', 'Field: {0} Error: {1}', $field, implode(',', $error)));
            });
        }
    }

    /**
     * Generates a random password.
     *
     * @return string
     */
    protected function _generateRandomPassword()
    {
        return str_replace('-', '', Text::uuid());
    }

    /**
     * Generates a random username based on a list of preexisting ones.
     *
     * @return string
     */
    protected function _generateRandomUsername()
    {
        return $this->_usernameSeed[array_rand($this->_usernameSeed)];
    }
}
