<?php
/**
 * Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Model\Behavior;

use CakeDC\Users\Email\EmailSender;
use CakeDC\Users\Exception\UserAlreadyActiveException;
use CakeDC\Users\Exception\UserNotActiveException;
use CakeDC\Users\Exception\UserNotFoundException;
use CakeDC\Users\Exception\WrongPasswordException;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Utility\Hash;
use InvalidArgumentException;

/**
 * Covers the password management features
 */
class PasswordBehavior extends BaseTokenBehavior
{
    /**
     * Constructor hook method.
     *
     * @param array $config The configuration settings provided to this behavior.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->Email = new EmailSender();
    }
    /**
     * Resets user token
     *
     * @param string $reference User username or email
     * @param array $options checkActive, sendEmail, expiration
     *
     * @return string
     * @throws InvalidArgumentException
     * @throws UserNotFoundException
     * @throws UserAlreadyActiveException
     */
    public function resetToken($reference, array $options = [])
    {
        if (empty($reference)) {
            throw new InvalidArgumentException(__d('CakeDC/Users', "Reference cannot be null"));
        }

        $expiration = Hash::get($options, 'expiration');
        if (empty($expiration)) {
            throw new InvalidArgumentException(__d('CakeDC/Users', "Token expiration cannot be empty"));
        }

        $user = $this->_getUser($reference);

        if (empty($user)) {
            throw new UserNotFoundException(__d('CakeDC/Users', "User not found"));
        }
        if (Hash::get($options, 'checkActive')) {
            if ($user->active) {
                throw new UserAlreadyActiveException(__d('CakeDC/Users', "User account already validated"));
            }
            $user->active = false;
            $user->activation_date = null;
        }
        if (Hash::get($options, 'ensureActive')) {
            if (!$user['active']) {
                throw new UserNotActiveException(__d('CakeDC/Users', "User not active"));
            }
        }
        $user->updateToken($expiration);
        $saveResult = $this->_table->save($user);
        $template = !empty($options['emailTemplate']) ? $options['emailTemplate'] : 'CakeDC/Users.reset_password';
        if (Hash::get($options, 'sendEmail')) {
            $this->Email->sendResetPasswordEmail($saveResult, null, $template);
        }

        return $saveResult;
    }

    /**
     * Get the user by email or username
     *
     * @param string $reference reference could be either an email or username
     * @return mixed user entity if found
     */
    protected function _getUser($reference)
    {
        return $this->_table->findByUsernameOrEmail($reference, $reference)->first();
    }

    /**
     * Change password method
     *
     * @param EntityInterface $user user data.
     * @throws WrongPasswordException
     * @return mixed
     */
    public function changePassword(EntityInterface $user)
    {
        try {
            $currentUser = $this->_table->get($user->id, [
                'contain' => []
            ]);
        } catch (RecordNotFoundException $e) {
            throw new UserNotFoundException(__d('CakeDC/Users', "User not found"));
        }

        if (!empty($user->current_password)) {
            if (!$user->checkPassword($user->current_password, $currentUser->password)) {
                throw new WrongPasswordException(__d('CakeDC/Users', 'The current password does not match'));
            }
            if ($user->current_password === $user->password_confirm) {
                throw new WrongPasswordException(__d(
                    'CakeDC/Users',
                    'You cannot use the current password as the new one'
                ));
            }
        }
        $user = $this->_table->save($user);
        if (!empty($user)) {
            $user = $this->_removeValidationToken($user);
        }

        return $user;
    }
}
