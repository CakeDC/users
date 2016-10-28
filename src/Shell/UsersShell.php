<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Shell;

use CakeDC\Users\Model\Entity\User;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Utility\Hash;
use Cake\Utility\Text;

/**
 * Shell with utilities for the Users Plugin
 *
 * @property \Users\Model\Table\Users Users
 */
class UsersShell extends Shell
{

    /**
     * Work as a seed for username generator
     *
     * @var array
     */
    protected $_usernameSeed = ['aayla', 'admiral', 'anakin', 'chewbacca', 'darthvader', 'hansolo', 'luke', 'obiwan', 'leia', 'r2d2'];

    /**
     * initialize callback
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->Users = $this->loadModel(Configure::read('Users.table'));
    }

    /**
     *
     * @return OptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->description(__d('CakeDC/Users', 'Utilities for CakeDC Users Plugin'))
            ->addSubcommand('activateUser')->description(__d('CakeDC/Users', 'Activate an specific user'))
            ->addSubcommand('addSuperuser')->description(__d('CakeDC/Users', 'Add a new superadmin user for testing purposes'))
            ->addSubcommand('addUser')->description(__d('CakeDC/Users', 'Add a new user'))
            ->addSubcommand('changeRole')->description(__d('CakeDC/Users', 'Change the role for an specific user'))
            ->addSubcommand('deactivateUser')->description(__d('CakeDC/Users', 'Deactivate an specific user'))
            ->addSubcommand('deleteUser')->description(__d('CakeDC/Users', 'Delete an specific user'))
            ->addSubcommand('passwordEmail')->description(__d('CakeDC/Users', 'Reset the password via email'))
            ->addSubcommand('resetAllPasswords')->description(__d('CakeDC/Users', 'Reset the password for all users'))
            ->addSubcommand('resetPassword')->description(__d('CakeDC/Users', 'Reset the password for an specific user'))
            ->addOptions([
                'username' => ['short' => 'u', 'help' => 'The username for the new user'],
                'password' => ['short' => 'p', 'help' => 'The password for the new user'],
                'email' => ['short' => 'e', 'help' => 'The email for the new user'],
                'role' => ['short' => 'r', 'help' => 'The role for the new user']
            ]);

        return $parser;
    }

    /**
     * Add a new user
     *
     * @return void
     */
    public function addUser()
    {
        $username = (empty($this->params['username']) ?
            $this->_generateRandomUsername() : $this->params['username']);

        $username = $this->Users->generateUniqueUsername($username);
        $password = (empty($this->params['password']) ?
            $this->_generateRandomPassword() : $this->params['password']);
        $email = (empty($this->params['email']) ? $username . '@example.com' : $this->params['email']);
        $defaultRole = Configure::check('CakeDC/Users.SimpleRbac.default_role')
            ? Configure::read('CakeDC/Users.SimpleRbac.default_role')
            : 'user';
        $role = (empty($this->params['role']) ? $defaultRole : $this->params['role']);
        $user = [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'active' => 1,
        ];

        $userEntity = $this->Users->newEntity($user);
        $savedUser = $this->Users->save($userEntity);
        $this->out(__d('CakeDC/Users', 'User added:'));
        $this->out(__d('CakeDC/Users', 'Id: {0}', $savedUser->id));
        $this->out(__d('CakeDC/Users', 'Username: {0}', $username));
        $this->out(__d('CakeDC/Users', 'Email: {0}', $savedUser->email));
        $this->out(__d('CakeDC/Users', 'Password: {0}', $password));
    }

    /**
     * Add a new superadmin user
     *
     * @return void
     */
    public function addSuperuser()
    {
        $username = $this->Users->generateUniqueUsername('superadmin');
        $password = $this->_generateRandomPassword();
        $user = [
            'username' => $username,
            'email' => $username . '@example.com',
            'password' => $password,
            'active' => 1,
        ];

        $userEntity = $this->Users->newEntity($user);
        $userEntity->is_superuser = true;
        $userEntity->role = 'superuser';
        $savedUser = $this->Users->save($userEntity);
        if (!empty($savedUser)) {
            $this->out(__d('CakeDC/Users', 'Superuser added:'));
            $this->out(__d('CakeDC/Users', 'Id: {0}', $savedUser->id));
            $this->out(__d('CakeDC/Users', 'Username: {0}', $username));
            $this->out(__d('CakeDC/Users', 'Email: {0}', $savedUser->email));
            $this->out(__d('CakeDC/Users', 'Password: {0}', $password));
        } else {
            $this->out(__d('CakeDC/Users', 'Superuser could not be added:'));

            collection($userEntity->errors())->each(function ($error, $field) {
                $this->out(__d('CakeDC/Users', 'Field: {0} Error: {1}', $field, implode(',', $error)));
            });
        }
    }

    /**
     * Reset password for all user
     *
     * Arguments:
     *
     * - Password to be set
     *
     * @return void
     */
    public function resetAllPasswords()
    {
        $password = Hash::get($this->args, 0);
        if (empty($password)) {
            $this->error(__d('CakeDC/Users', 'Please enter a password.'));
        }
        $hashedPassword = $this->_generatedHashedPassword($password);
        $this->Users->updateAll(['password' => $hashedPassword], ['id IS NOT NULL']);
        $this->out(__d('CakeDC/Users', 'Password changed for all users'));
        $this->out(__d('CakeDC/Users', 'New password: {0}', $password));
    }

    /**
     * Reset password for a user
     *
     * Arguments:
     *
     * - Username
     * - Password to be set
     *
     * @return void
     */
    public function resetPassword()
    {
        $username = Hash::get($this->args, 0);
        $password = Hash::get($this->args, 1);
        if (empty($username)) {
            $this->error(__d('CakeDC/Users', 'Please enter a username.'));
        }
        if (empty($password)) {
            $this->error(__d('CakeDC/Users', 'Please enter a password.'));
        }
        $data = [
            'password' => $password
        ];
        $this->_updateUser($username, $data);
        $this->out(__d('CakeDC/Users', 'Password changed for user: {0}', $username));
        $this->out(__d('CakeDC/Users', 'New password: {0}', $password));
    }

    /**
     * Change role for a user
     *
     * Arguments:
     *
     * - Username
     * - Role to be set
     *
     * @return void
     */
    public function changeRole()
    {
        $username = Hash::get($this->args, 0);
        $role = Hash::get($this->args, 1);
        if (empty($username)) {
            $this->error(__d('CakeDC/Users', 'Please enter a username.'));
        }
        if (empty($role)) {
            $this->error(__d('CakeDC/Users', 'Please enter a role.'));
        }
        $data = [
            'role' => $role
        ];
        $savedUser = $this->_updateUser($username, $data);
        $this->out(__d('CakeDC/Users', 'Role changed for user: {0}', $username));
        $this->out(__d('CakeDC/Users', 'New role: {0}', $savedUser->role));
    }

    /**
     * Activate an specific user
     *
     * Arguments:
     *
     * - Username
     *
     * @return void
     */
    public function activateUser()
    {
        $user = $this->_changeUserActive(true);
        $this->out(__d('CakeDC/Users', 'User was activated: {0}', $user->username));
    }

    /**
     * De-activate an specific user
     *
     * Arguments:
     *
     * - Username
     *
     * @return void
     */
    public function deactivateUser()
    {
        $user = $this->_changeUserActive(false);
        $this->out(__d('CakeDC/Users', 'User was de-activated: {0}', $user->username));
    }

    /**
     * Reset password via email for user
     *
     * @return void
     */
    public function passwordEmail()
    {
        $reference = Hash::get($this->args, 0);
        if (empty($reference)) {
            $this->error(__d('CakeDC/Users', 'Please enter a username or email.'));
        }
        $resetUser = $this->Users->resetToken($reference, [
            'expiration' => Configure::read('Users.Token.expiration'),
            'checkActive' => false,
            'sendEmail' => true,
        ]);
        if ($resetUser) {
            $msg = __d('CakeDC/Users', 'Please ask the user to check the email to continue with password reset process');
            $this->out($msg);
        } else {
            $msg = __d('CakeDC/Users', 'The password token could not be generated. Please try again');
            $this->error($msg);
        }
    }

    /**
     * Change user active field
     *
     * @param bool $active active value
     * @return bool
     */
    protected function _changeUserActive($active)
    {
        $username = Hash::get($this->args, 0);
        if (empty($username)) {
            $this->error(__d('CakeDC/Users', 'Please enter a username.'));
        }
        $data = [
            'active' => $active
        ];

        return $this->_updateUser($username, $data);
    }

    /**
     * Update user by username
     *
     * @param string $username username
     * @param array $data data
     * @return bool
     */
    protected function _updateUser($username, $data)
    {
        $user = $this->Users->find()->where(['username' => $username])->first();
        if (empty($user)) {
            $this->error(__d('CakeDC/Users', 'The user was not found.'));
        }
        $user = $this->Users->patchEntity($user, $data);
        collection($data)->filter(function ($value, $field) use ($user) {
            return !$user->accessible($field);
        })->each(function ($value, $field) use (&$user) {
            $user->{$field} = $value;
        });
        $savedUser = $this->Users->save($user);

        return $savedUser;
    }

    /**
     * Delete an specific user and associated social accounts
     *
     * @return void
     */
    public function deleteUser()
    {
        $username = Hash::get($this->args, 0);
        if (empty($username)) {
            $this->error(__d('CakeDC/Users', 'Please enter a username.'));
        }
        $user = $this->Users->find()->where(['username' => $username])->first();
        if (isset($this->Users->SocialAccounts)) {
            $this->Users->SocialAccounts->deleteAll(['user_id' => $user->id]);
        }
        $deleteUser = $this->Users->delete($user);
        if (!$deleteUser) {
            $this->error(__d('CakeDC/Users', 'The user {0} was not deleted. Please try again', $username));
        }
        $this->out(__d('CakeDC/Users', 'The user {0} was deleted successfully', $username));
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

    /**
     * Hash a password
     *
     * @param string $password password
     * @return string
     */
    protected function _generatedHashedPassword($password)
    {
        return (new User)->hashPassword($password);
    }
    //add filters LIKE in username and email to some tasks
    // --force to ignore "you are about to do X to Y users"
}
