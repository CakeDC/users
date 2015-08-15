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

namespace Users\Model\Table\Traits;

use Cake\Datasource\EntityInterface;
use Cake\Network\Email\Email;
use Cake\Utility\Hash;
use DateTime;
use InvalidArgumentException;
use Users\Exception\TokenExpiredException;
use Users\Exception\UserAlreadyActiveException;
use Users\Exception\UserNotFoundException;
use Users\Model\Entity\User;

/**
 * Covers the login, logout and social login
 *
 */
trait RegisterTrait
{

    /**
     * Registers an user.
     *
     * @param EntityInterface $user User information
     * @param array $data User information
     * @param array $options ['tokenExpiration]
     * @return bool|EntityInterface
     * @throws InvalidArgumentException
     * @todo: move into new behavior
     */
    public function register($user, $data, $options)
    {
        $validateEmail = Hash::get($options, 'validate_email');
        $validator = Hash::get($options, 'validator') ?: 'register';
        if ($validateEmail) {
            $validator = 'email';
        }
        $user = $this->patchEntity($user, $data, ['validate' => $validator]);

        $tokenExpiration = Hash::get($options, 'token_expiration');
        $useTos = Hash::get($options, 'use_tos');
        if ($useTos && !$user->tos) {
            throw new InvalidArgumentException(__d('Users', 'The "tos" property is not present'));
        }

        if (!empty($user['tos'])) {
            $user->tos_date = new DateTime();
        }
        $user->validated = false;
        //@todo mov updateActive to afterSave?
        $this->_updateActive($user, $validateEmail, $tokenExpiration);
        $this->isValidateEmail = $validateEmail;
        $userSaved = $this->save($user);
        if ($userSaved && $validateEmail) {
            $this->sendValidationEmail($user);
        }
        return $userSaved;
    }

    /**
     * DRY for update active and token based on validateEmail flag
     *
     * @param type $user Reference of user to be updated.
     * @param type $validateEmail email user to validate.
     * @param type $tokenExpiration token to be updated.
     * @return void
     */
    protected function _updateActive(&$user, $validateEmail, $tokenExpiration)
    {
        $emailValidated = $user['validated'];
        if (!$emailValidated && $validateEmail) {
            $user['active'] = false;
            $user->updateToken($tokenExpiration);
        } else {
            $user['active'] = true;
            $user['activation_date'] = new DateTime();
        }
    }

    /**
     * Send the validation email to the newly registered user
     *
     * @param EntityInterface $user User entity
     * @param Email $email instance, if null the default email configuration with the
     * Users.validation template will be used, so set a ->template() if you pass an Email
     * instance
     * @return array email send result
     */
    public function sendValidationEmail(EntityInterface $user, Email $email = null)
    {
        $firstName = isset($user['first_name'])? $user['first_name'] . ', ' : '';
        $subject = __d('Users', '{0}Your account validation link', $firstName);
        return $this->getEmailInstance($email)
                ->to($user['email'])
                ->subject($subject)
                ->viewVars($user->toArray())
                ->send();
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
        $user = $this->find()
            ->select(['token_expires', 'id', 'active', 'token'])
            ->where(['token' => $token])
            ->first();
        if (!empty($user)) {
            if (!$user->tokenExpired()) {
                if (!empty($callback) && method_exists($this, $callback)) {
                    return $this->{$callback}($user);
                } else {
                    return $user;
                }
            } else {
                throw new TokenExpiredException(__d('Users', "Token has already expired user with no token"));
            }
        } else {
            throw new UserNotFoundException(__d('Users', "User not found for the given token and email."));
        }
    }

    /**
     * Activates an user
     *
     * @param EntityInterface $user user object.
     * @return mixed User entity or bool false if the user could not be activated
     * @throws UserAlreadyActiveException
     * @todo: move into new behavior
     */
    public function activateUser(EntityInterface $user)
    {
        if ($user->active) {
            throw new UserAlreadyActiveException(__d('Users', "User account already validated"));
        }
        $user = $this->_removesValidationToken($user);
        $user->activation_date = new DateTime();
        $user->active = true;
        $result = $this->save($user);

        return $result;
    }

    /**
     * Removes user token for validation
     *
     * @param User $user user object.
     * @return User
     *
     * @todo: move into new behavior
     */
    protected function _removesValidationToken(EntityInterface $user)
    {
        $user->token = null;
        $user->token_expires = null;
        $result = $this->save($user);

        return $result;
    }
}
