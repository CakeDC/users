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

namespace Users\Shell;

use Cake\Auth\DefaultPasswordHasher;
use Cake\Console\Shell;
use Cake\Utility\Hash;
use Users\Model\Entity\User;

/**
 * Shell with utilities for the Users Plugin
 *
 * @property \Users\Model\Table\Users Users
 */
class UsersShell extends Shell
{
    /**
     * initialize callback
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadModel('Users.Users');
    }

    /**
     *
     * @return OptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->description(__d('Users', 'Utilities for CakeDC Users Plugin'))
                ->addSubcommand('addSuperuser')->description(__d('Users', 'Add a new superadmin user for testing purposes'))
                ->addSubcommand('changeRole')->description(__d('Users', 'Change the role for an specific user'))
                ->addSubcommand('resetAllPasswords')->description(__d('Users', 'Reset the password for all users'))
                ->addSubcommand('resetPassword')->description(__d('Users', 'Reset the password for an specific user'))
                ->addSubcommand('activateUser')->description(__d('Users', 'Activate an specific user'))
                ->addSubcommand('deactivateUser')->description(__d('Users', 'Deactivate an  specific user'));
        return $parser;
    }

    /**
     * Add a new superadmin user
     *
     * @return void
     */
    public function addSuperuser()
    {
        $username = $this->Users->generateUniqueUsername('superadmin');
        $password = str_replace('-', '', \Cake\Utility\Text::uuid());
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
        $this->out(__d('Users', 'Superuser added:'));
        $this->out(__d('Users', 'Id: {0}', $savedUser->id));
        $this->out(__d('Users', 'Username: {0}', $username));
        $this->out(__d('Users', 'Email: {0}', $savedUser->email));
        $this->out(__d('Users', 'Password: {0}', $password));
    }

    //addUser

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
            $this->error(__d('Users', 'Please enter a password.'));
        }
        $hashedPassword = (new User)->hashPassword($password);
        $this->Users->updateAll(['password' => $hashedPassword], ['id IS NOT NULL']);
        $this->out(__d('Users', 'Password changed for all users'));
        $this->out(__d('Users', 'New password: {0}', $password));
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
            $this->error(__d('Users', 'Please enter a username.'));
        }
        if (empty($password)) {
            $this->error(__d('Users', 'Please enter a password.'));
        }
        $data = [
            'password' => $password
        ];
        $this->_updateUser($username, $data);
        $this->out(__d('Users', 'Password changed for user: {0}', $username));
        $this->out(__d('Users', 'New password: {0}', $password));
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
            $this->error(__d('Users', 'Please enter a username.'));
        }
        if (empty($role)) {
            $this->error(__d('Users', 'Please enter a role.'));
        }
        $data = [
            'role' => $role
        ];
        $savedUser = $this->_updateUser($username, $data);
        $this->out(__d('Users', 'Role changed for user: {0}', $username));
        $this->out(__d('Users', 'New role: {0}', $savedUser->role));
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
        $this->out(__d('Users', 'User was activated: {0}', $user->username));

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
        $this->out(__d('Users', 'User was de-activated: {0}', $user->username));
    }

    /**
     * Change user active field
     *
     * @param $active
     */
    protected function _changeUserActive($active)
    {
        $username = Hash::get($this->args, 0);
        if (empty($username)) {
            $this->error(__d('Users', 'Please enter a username.'));
        }
        $data = [
            'active' => $active
        ];
        return $this->_updateUser($username, $data);
    }

    /**
     * Update user by username
     *
     * @param $username
     * @param $data
     */
    protected function _updateUser($username, $data)
    {
        /** @var \Users\Model\Entity\User */
        $user = $this->Users->find()->where(['Users.username' => $username])->first();
        if (empty($user)) {
            $this->error(__d('Users', 'The user was not found.'));
        }
        $user = $this->Users->patchEntity($user, $data);
        $savedUser = $this->Users->save($user);
        return $savedUser;
    }


    //deleteUser user
    //add filters LIKE in username and email to some tasks
    // --force to ignore "you are about to do X to Y users"
}
