<?php
declare(strict_types=1);

namespace CakeDC\Users\Command\Logic;

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
    protected array $usernameSeed = [
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
     * @param array $template template with deafault user values
     * @return void
     */
    protected function createUser(array $template): void
    {
        /**
         * @var \CakeDC\Users\Model\Table\UsersTable $UsersTable
         */
        $UsersTable = $this->getTableLocator()->get('Users');
        $username = $this->args->getOption('username');
        $password = $this->args->getOption('password');
        $email = $this->args->getOption('email');
        $role = $this->args->getOption('role');
        if (empty($username)) {
            $username = empty($template['username'])
                ? $this->generateRandomUsername()
                : $template['username'];
        }

        $password = $password ?: $this->generateRandomPassword();
        $email = $email ?: $username . '@example.com';
        $role = $role ?: $template['role'];

        /** @var \CakeDC\Users\Model\Behavior\SocialBehavior $socialBehavior */
        $socialBehavior = $UsersTable->getBehavior('Social');

        $user = [
            'username' => $socialBehavior->generateUniqueUsername($username),
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
                $this->io->out(__d('cake_d_c/users', 'Superuser added:'));
            } else {
                $this->io->out(__d('cake_d_c/users', 'User added:'));
            }
            $this->io->out(__d('cake_d_c/users', 'Id: {0}', $savedUser->id));
            $this->io->out(__d('cake_d_c/users', 'Username: {0}', $savedUser->username));
            $this->io->out(__d('cake_d_c/users', 'Email: {0}', $savedUser->email));
            $this->io->out(__d('cake_d_c/users', 'Role: {0}', $savedUser->role));
            $this->io->out(__d('cake_d_c/users', 'Password: {0}', $password));
        } else {
            $this->io->out(__d('cake_d_c/users', 'User could not be added:'));

            collection($userEntity->getErrors())->each(function ($error, $field): void {
                $this->io->out(__d('cake_d_c/users', 'Field: {0} Error: {1}', $field, implode(',', $error)));
            });
        }
    }

    /**
     * Generates a random password.
     *
     * @return string
     */
    protected function generateRandomPassword(): string
    {
        return str_replace('-', '', Text::uuid());
    }

    /**
     * Generates a random username based on a list of preexisting ones.
     *
     * @return string
     */
    protected function generateRandomUsername(): string
    {
        return $this->usernameSeed[array_rand($this->usernameSeed)];
    }
}
