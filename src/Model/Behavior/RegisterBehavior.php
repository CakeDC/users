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

namespace Users\Model\Behavior;

use Cake\Datasource\EntityInterface;
use Cake\Utility\Hash;
use DateTime;
use InvalidArgumentException;
use Users\Exception\TokenExpiredException;
use Users\Exception\UserAlreadyActiveException;
use Users\Exception\UserNotFoundException;
use Users\Model\Behavior\Behavior;
use Users\Model\Entity\User;

/**
 * Covers the user registration
 */
class RegisterBehavior extends Behavior
{
    /**
     * Registers an user.
     *
     * @param EntityInterface $user User information
     * @param array $data User information
     * @param array $options ['tokenExpiration]
     * @return bool|EntityInterface
     * @throws InvalidArgumentException
     */
    public function register($user, $data, $options)
    {
        $validateEmail = Hash::get($options, 'validate_email');
        $validator = Hash::get($options, 'validator') ?: 'register';
        if ($validateEmail) {
            $validator = 'email';
        }
        $user = $this->_table->patchEntity($user, $data, ['validate' => $validator]);

        $tokenExpiration = Hash::get($options, 'token_expiration');
        $useTos = Hash::get($options, 'use_tos');
        if ($useTos && !$user->tos) {
            throw new InvalidArgumentException(__d('Users', 'The "tos" property is not present'));
        }

        if (!empty($user['tos'])) {
            $user->tos_date = new DateTime();
        }
        $user->validated = false;
        //@todo move updateActive to afterSave?
        $user = $this->_updateActive($user, $validateEmail, $tokenExpiration);
        $this->_table->isValidateEmail = $validateEmail;
        $userSaved = $this->_table->save($user);
        if ($userSaved && $validateEmail) {
            $this->_sendEmail($user, __d('Users', 'Your account validation link'));
        }
        return $userSaved;
    }

    /**
     * Validates token and return user
     *
     * @param type $token toke to be validated.
     * @param null $callback function that will be returned.
     * @throws TokenExpiredException when token has expired.
     * @throws UserNotFoundException when user isn't found.
     * @return User $user
     */
    public function validate($token, $callback = null)
    {
        $user = $this->_table->find()
            ->select(['token_expires', 'id', 'active', 'token'])
            ->where(['token' => $token])
            ->first();
        if (empty($user)) {
            throw new UserNotFoundException(__d('Users', "User not found for the given token and email."));
        }
        if ($user->tokenExpired()) {
            throw new TokenExpiredException(__d('Users', "Token has already expired user with no token"));
        }
        if (!method_exists($this, $callback)) {
            return $user;
        }

        return $this->_table->{$callback}($user);
    }

    /**
     * Activates an user
     *
     * @param EntityInterface $user user object.
     * @return mixed User entity or bool false if the user could not be activated
     * @throws UserAlreadyActiveException
     */
    public function activateUser(EntityInterface $user)
    {
        if ($user->active) {
            throw new UserAlreadyActiveException(__d('Users', "User account already validated"));
        }
        $user = $this->_removeValidationToken($user);
        $user->activation_date = new DateTime();
        $user->active = true;
        $result = $this->_table->save($user);

        return $result;
    }
}
