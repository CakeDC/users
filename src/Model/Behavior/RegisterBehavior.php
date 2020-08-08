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
use Cake\Event\Event;
use Cake\Mailer\MailerAwareTrait;
use Cake\Validation\Validator;
use CakeDC\Users\Exception\TokenExpiredException;
use CakeDC\Users\Exception\UserAlreadyActiveException;
use CakeDC\Users\Exception\UserNotFoundException;

/**
 * Covers the user registration
 */
class RegisterBehavior extends BaseTokenBehavior
{
    use MailerAwareTrait;

    /**
     * @var bool
     */
    protected $validateEmail;
    /**
     * @var bool
     */
    protected $useTos;

    /**
     * Constructor hook method.
     *
     * @param array $config The configuration settings provided to this behavior.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->validateEmail = (bool)Configure::read('Users.Email.validate');
        $this->useTos = (bool)Configure::read('Users.Tos.required');
    }

    /**
     * Registers an user.
     *
     * @param \Cake\Datasource\EntityInterface $user User information
     * @param array $data User information
     * @param array $options ['tokenExpiration]
     * @return bool|\Cake\Datasource\EntityInterface
     */
    public function register($user, $data, $options)
    {
        $validateEmail = $options['validate_email'] ?? null;
        $tokenExpiration = $options['token_expiration'] ?? null;
        $validator = $options['validator'] ?? null;
        $user = $this->_table->patchEntity(
            $user,
            $data,
            ['validate' => $validator ?: $this->getRegisterValidators($options)]
        );
        $user['role'] = Configure::read('Users.Registration.defaultRole') ?: 'user';
        $user->validated = false;
        //@todo move updateActive to afterSave?
        $user = $this->_updateActive($user, $validateEmail, $tokenExpiration);
        $this->_table->isValidateEmail = $validateEmail;
        $userSaved = $this->_table->save($user);
        if ($userSaved && $validateEmail) {
            $this->_sendValidationEmail($user);
        }

        return $userSaved;
    }

    /**
     * Validates token and return user
     *
     * @param string $token toke to be validated.
     * @param null $callback function that will be returned.
     * @throws \CakeDC\Users\Exception\TokenExpiredException when token has expired.
     * @throws \CakeDC\Users\Exception\UserNotFoundException when user isn't found.
     * @return \Cake\Datasource\EntityInterface $user
     */
    public function validate($token, $callback = null)
    {
        $user = $token ? $this->_table->find()
            ->select(['token_expires', 'id', 'active', 'token'])
            ->where(['token' => $token])
            ->first() : null;
        if (empty($user)) {
            throw new UserNotFoundException(__d('cake_d_c/users', 'User not found for the given token and email.'));
        }
        if ($user->tokenExpired()) {
            throw new TokenExpiredException(__d('cake_d_c/users', 'Token has already expired user with no token'));
        }
        if (!method_exists($this, (string)$callback)) {
            return $user;
        }

        return $this->_table->{$callback}($user);
    }

    /**
     * Activates an user
     *
     * @param \Cake\Datasource\EntityInterface $user user object.
     * @return mixed User entity or bool false if the user could not be activated
     * @throws \CakeDC\Users\Exception\UserAlreadyActiveException
     */
    public function activateUser(EntityInterface $user)
    {
        if ($user->active) {
            throw new UserAlreadyActiveException(__d('cake_d_c/users', 'User account already validated'));
        }
        $user->activation_date = new \DateTime();
        $user->token_expires = null;
        $user->active = true;
        $result = $this->_table->save($user);

        return $result;
    }

    /**
     * buildValidator
     *
     * @param \Cake\Event\Event $event event
     * @param \Cake\Validation\Validator $validator validator
     * @param string $name name
     * @return \Cake\Validation\Validator
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
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @param bool $validateEmail true when email needs to be required
     * @return \Cake\Validation\Validator
     */
    protected function _emailValidator(Validator $validator, $validateEmail)
    {
        $this->validateEmail = $validateEmail;
        $validator
            ->add('email', 'valid', ['rule' => 'email'])
            ->notBlank('email', __d('cake_d_c/users', 'This field is required'), function ($context) {
                return $this->validateEmail;
            });

        return $validator;
    }

    /**
     * Tos validator
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    protected function _tosValidator(Validator $validator)
    {
        $validator
            ->requirePresence('tos', 'create')
            ->notBlank('tos');

        return $validator;
    }

    /**
     * Returns the list of validators
     *
     * @param array $options Array of options ['validate_email' => true/false, 'use_tos' => true/false]
     * @return \Cake\Validation\Validator
     */
    public function getRegisterValidators($options)
    {
        $validateEmail = $options['validate_email'] ?? null;
        $useTos = $options['use_tos'] ?? null;

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
}
