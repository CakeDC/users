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

use Cake\Console\Shell;

/**
 * Shell with utilities for the Users Plugin
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
                ->addSubcommand('addSuperuser')->description(__d('Users', 'Add a new superadmin user for testing purposes'));
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

    //resetAllPasswords to XXX
    //resetPassword user to XXX
    //addUser
    //changeRole user to XXX
    //deleteUser user
    //deactivateUser user
    //activateUser user
    //add filters LIKE in username and email to some tasks
    // --force to ignore "you are about to do X to Y users"
}
