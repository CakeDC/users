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
use CakeDC\Users\Exception\TokenExpiredException;
use CakeDC\Users\Exception\UserAlreadyActiveException;
use CakeDC\Users\Exception\UserNotFoundException;
use CakeDC\Users\Model\Entity\User;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use DateTime;
use InvalidArgumentException;

/**
 * Covers the user registration
 */
class RegisterBehavior extends BaseTokenBehavior
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
        $this->validateEmail = (bool)Configure::read('Users.Email.validate');
        $this->useTos = (bool)Configure::read('Users.Tos.required');
        $this->Email = new EmailSender();
    }

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
        $tokenExpiration = Hash::get($options, 'token_expiration');
        $emailClass = Hash::get($options, 'email_class');
        $user = $this->_table->patchEntity(
            $user,
            $data,
            ['validate' => Hash::get($options, 'validator') ?: $this->getRegisterValidators($options)]
        );
        $user['role'] = Configure::read('Users.Registration.defaultRole') ?: 'user';
        $user->validated = false;
        //@todo move updateActive to afterSave?
        $user = $this->_updateActive($user, $validateEmail, $tokenExpiration);
        $this->_table->isValidateEmail = $validateEmail;
        $userSaved = $this->_table->save($user);
        if ($userSaved && $validateEmail) {
            $this->Email->sendValidationEmail($user, $emailClass);
        }

        return $userSaved;
    }

    /**
     * Validates token and return user
     *
     * @param string $token toke to be validated.
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
            throw new UserNotFoundException(__d('CakeDC/Users', "User not found for the given token and email."));
        }
        if ($user->tokenExpired()) {
            throw new TokenExpiredException(__d('CakeDC/Users', "Token has already expired user with no token"));
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
            throw new UserAlreadyActiveException(__d('CakeDC/Users', "User account already validated"));
        }
        $user->activation_date = new DateTime();
        $user->token_expires = null;
        $user->active = true;
        $result = $this->_table->save($user);

        return $result;
    }

    /**
     * buildValidator
     *
     * @param Event $event event
     * @param Validator $validator validator
     * @param string $name name
     * @return Validator
     */
    public function buildValidator(Event $event, Validator $validator, $name)
    {
        if ($name === 'default') {
            return $this->_emailValidator($validator, $this->validateEmail);
        }

        return $validator;
    }

    /**
     * Email validator
     *
     * @param Validator $validator Validator instance.
     * @param bool $validateEmail true when email needs to be required
     * @return Validator
     */
    protected function _emailValidator(Validator $validator, $validateEmail)
    {
        $this->validateEmail = $validateEmail;
        $validator
            ->add('email', 'valid', ['rule' => 'email'])
            ->notEmpty('email', __d('Users', 'This field is required'), function ($context) {
                return $this->validateEmail;
            });

        return $validator;
    }

    /**
     * Tos validator
     *
     * @param Validator $validator Validator instance.
     * @return Validator
     */
    protected function _tosValidator(Validator $validator)
    {
        $validator
            ->requirePresence('tos', 'create')
            ->notEmpty('tos');

        return $validator;
    }

    /**
     * Returns the list of validators
     *
     * @param array $options Array of options ['validate_email' => true/false, 'use_tos' => true/false]
     * @return Validator
     */
    public function getRegisterValidators($options)
    {
        $validateEmail = Hash::get($options, 'validate_email');
        $useTos = Hash::get($options, 'use_tos');

        $validator = $this->_table->validationDefault(new Validator());
        $validator = $this->_table->validationRegister($validator);
        if ($useTos) {
            $validator = $this->_tosValidator($validator);
        }

        if ($validateEmail) {
            $validator = $this->_emailValidator($validator, $validateEmail);
        }

        return $validator;
    }
}
