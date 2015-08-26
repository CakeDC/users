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

namespace CakeDC\Users\Model\Behavior;

use Cake\Datasource\EntityInterface;
use Cake\Network\Email\Email;
use Cake\Utility\Hash;
use InvalidArgumentException;
use CakeDC\Users\Exception\UserAlreadyActiveException;
use CakeDC\Users\Exception\UserNotFoundException;
use CakeDC\Users\Exception\WrongPasswordException;
use CakeDC\Users\Model\Behavior\Behavior;

/**
 * Covers the password management features
 */
class PasswordBehavior extends Behavior
{
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
            throw new InvalidArgumentException(__d('Users', "Reference cannot be null"));
        }

        if (empty(Hash::get($options, 'expiration'))) {
            throw new InvalidArgumentException(__d('Users', "Token expiration cannot be null"));
        }

        $user = $this->_getUser($reference);

        if (empty($user)) {
            throw new UserNotFoundException(__d('Users', "User not found"));
        }
        if (Hash::get($options, 'checkActive')) {
            if ($user->active) {
                throw new UserAlreadyActiveException("User account already validated");
            }
            $user->active = false;
            $user->activation_date = null;
        }
        $user->updateToken(Hash::get($options, 'expiration'));
        $saveResult = $this->_table->save($user);
        if (Hash::get($options, 'sendEmail')) {
            $this->sendResetPasswordEmail($saveResult);
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
        return $this->_table->findAllByUsernameOrEmail($reference, $reference)->first();
    }

    /**
     * Send the reset password email
     *
     * @param EntityInterface $user User entity
     * @param Email $email instance, if null the default email configuration with the
     * Users.validation template will be used, so set a ->template() if you pass an Email
     * instance
     * @return array email send result
     */
    public function sendResetPasswordEmail(EntityInterface $user, Email $email = null)
    {
        $firstName = isset($user['first_name'])? $user['first_name'] . ', ' : '';
        $subject = __d('Users', '{0}Your reset password link', $firstName);
        return $this->_getEmailInstance($email)
                ->template('Users.reset_password')
                ->to($user['email'])
                ->subject($subject)
                ->viewVars($user->toArray())
                ->send();
    }

    /**
     * Change password method
     *
     * @param EntityInterface $user user data.
     * @return mixed
     */
    public function changePassword(EntityInterface $user)
    {
        $currentUser = $this->_table->get($user->id, [
            'contain' => []
        ]);

        if (!empty($user->current_password)) {
            if (!$user->checkPassword($user->current_password, $currentUser->password)) {
                throw new WrongPasswordException(__d('Users', 'The old password does not match'));
            }
        }
        $user = $this->_table->save($user);
        if (!empty($user)) {
            $user = $this->_removeValidationToken($user);
        }
        return $user;
    }
}
