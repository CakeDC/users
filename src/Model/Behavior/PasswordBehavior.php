<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Model\Behavior;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Mailer\MailerAwareTrait;
use CakeDC\Users\Exception\UserAlreadyActiveException;
use CakeDC\Users\Exception\UserNotActiveException;
use CakeDC\Users\Exception\UserNotFoundException;
use CakeDC\Users\Exception\WrongPasswordException;

/**
 * Covers the password management features
 */
class PasswordBehavior extends BaseTokenBehavior
{
    use MailerAwareTrait;

    /**
     * Resets user token
     *
     * @param string $reference User username or email
     * @param array $options checkActive, sendEmail, expiration
     * @return string
     * @throws \InvalidArgumentException
     * @throws \CakeDC\Users\Exception\UserNotFoundException
     * @throws \CakeDC\Users\Exception\UserAlreadyActiveException
     */
    public function resetToken($reference, array $options = [])
    {
        if (empty($reference)) {
            throw new \InvalidArgumentException(__d('cake_d_c/users', 'Reference cannot be null'));
        }

        $expiration = $options['expiration'] ?? null;
        if (empty($expiration)) {
            throw new \InvalidArgumentException(__d('cake_d_c/users', 'Token expiration cannot be empty'));
        }

        $user = $this->_getUser($reference);

        if (empty($user)) {
            throw new UserNotFoundException(__d('cake_d_c/users', 'User not found'));
        }
        if ($options['checkActive'] ?? false) {
            if ($user->active) {
                throw new UserAlreadyActiveException(__d('cake_d_c/users', 'User account already validated'));
            }
            $user->active = false;
            $user->activation_date = null;
        }
        if ($options['ensureActive'] ?? false) {
            if (!$user['active']) {
                throw new UserNotActiveException(__d('cake_d_c/users', 'User not active'));
            }
        }
        $user->updateToken($expiration);
        $saveResult = $this->_table->save($user);
        if ($options['sendEmail'] ?? false) {
            switch ($options['type'] ?? null) {
                case 'email':
                    $this->_sendValidationEmail($user);
                    break;
                case 'password':
                    $this->_sendResetPasswordEmail($user);
                    break;
            }
        }

        return $saveResult;
    }

    /**
     * Send the reset password related email link
     *
     * @param \Cake\Datasource\EntityInterface $user user
     * @return void
     */
    protected function _sendResetPasswordEmail($user)
    {
        $this
            ->getMailer(Configure::read('Users.Email.mailerClass') ?: 'CakeDC/Users.Users')
            ->send('resetPassword', [$user]);
    }

    /**
     * Wrapper for mailer
     *
     * @param \Cake\Datasource\EntityInterface $user user
     * @return void
     */
    protected function _sendValidationEmail($user)
    {
        $mailer = Configure::read('Users.Email.mailerClass') ?: 'CakeDC/Users.Users';
        $this
            ->getMailer($mailer)
            ->send('validation', [$user]);
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
     * @param \Cake\Datasource\EntityInterface $user user data.
     * @throws \CakeDC\Users\Exception\WrongPasswordException
     * @return mixed
     */
    public function changePassword(EntityInterface $user)
    {
        try {
            $currentUser = $this->_table->get($user->id, [
                'contain' => [],
            ]);
        } catch (RecordNotFoundException $e) {
            throw new UserNotFoundException(__d('cake_d_c/users', 'User not found'));
        }

        if (!empty($user->current_password)) {
            if (!$user->checkPassword($user->current_password, $currentUser->password)) {
                throw new WrongPasswordException(__d('cake_d_c/users', 'The current password does not match'));
            }
            if ($user->current_password === $user->password_confirm) {
                throw new WrongPasswordException(__d(
                    'cake_d_c/users',
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
