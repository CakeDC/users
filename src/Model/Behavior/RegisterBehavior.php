<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Model\Behavior;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Mailer\MailerAwareTrait;
use Cake\Validation\Validator;
use CakeDC\Users\Exception\TokenExpiredException;
use CakeDC\Users\Exception\UserAlreadyActiveException;
use CakeDC\Users\Exception\UserNotFoundException;
use CakeDC\Users\Model\Entity\User;
use DateTime;

/**
 * Covers the user registration
 */
class RegisterBehavior extends BaseTokenBehavior
{
    use MailerAwareTrait;

    /**
     * @var bool
     */
    protected bool $validateEmail = true;
    /**
     * @var bool
     */
    protected bool $useTos = true;

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
     * @return \Cake\Datasource\EntityInterface|bool
     */
    public function register(EntityInterface $user, array $data, array $options): EntityInterface|bool
    {
        $validateEmail = (bool)($options['validate_email'] ?? null);
        $tokenExpiration = $options['token_expiration'] ?? null;
        $validator = $options['validator'] ?? null;
        if (is_string($validator)) {
            $validate = $validator;
        } else {
            $this->table->setValidator('current', $validator ?: $this->getRegisterValidators($options));
            $validate = 'current';
        }

        $user = $this->table->patchEntity(
            $user,
            $data,
            ['validate' => $validate],
        );
        $user['role'] = Configure::read('Users.Registration.defaultRole') ?: 'user';
        $user->validated = false;
        //@todo move updateActive to afterSave?
        $user = $this->updateActive($user, $validateEmail, $tokenExpiration);
        $this->table->isValidateEmail = $validateEmail;
        $userSaved = $this->table->save($user);
        if ($userSaved && $validateEmail) {
            $this->sendValidationEmail($user);
        }

        return $userSaved;
    }

    /**
     * Validates token and return user
     *
     * @param string|null $token toke to be validated.
     * @param string|null $callback function that will be returned.
     * @throws \CakeDC\Users\Exception\TokenExpiredException when token has expired.
     * @throws \CakeDC\Users\Exception\UserNotFoundException when user isn't found.
     * @return \Cake\Datasource\EntityInterface $user
     */
    public function validate(?string $token = null, ?string $callback = null): EntityInterface
    {
        $user = $token ? $this->table->find()
            ->select(['token_expires', 'id', 'active', 'token'])
            ->where(['token' => $token])
            ->first() : null;
        if (empty($user)) {
            throw new UserNotFoundException(__d('cake_d_c/users', 'User not found for the given token and email.'));
        }
        if ($user instanceof User && $user->tokenExpired()) {
            throw new TokenExpiredException(__d('cake_d_c/users', 'Token has already expired user with no token'));
        }
        if (!method_exists($this, (string)$callback)) {
            return $user;
        }

        return $this->table->getBehavior('Register')->{$callback}($user);
    }

    /**
     * Activates an user
     *
     * @param \Cake\Datasource\EntityInterface $user user object.
     * @return mixed User entity or bool false if the user could not be activated
     * @throws \CakeDC\Users\Exception\UserAlreadyActiveException
     */
    public function activateUser(EntityInterface $user): mixed
    {
        if ($user->active) {
            throw new UserAlreadyActiveException(__d('cake_d_c/users', 'User account already validated'));
        }
        $user->set('token', null);
        $user->set('token_expires', null);
        $user->set('activation_date', new DateTime());
        $user->set('active', true);

        return $this->table->save($user);
    }

    /**
     * buildValidator
     *
     * @param \Cake\Event\Event $event event
     * @param \Cake\Validation\Validator $validator validator
     * @param string $name name
     * @return void
     */
    public function buildValidator(EventInterface $event, Validator $validator, string $name): void
    {
        if ($name === 'default') {
            $event->setResult($this->emailValidator($validator, $this->validateEmail));

            return;
        }

        $event->setResult($validator);
    }

    /**
     * Email validator
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @param bool $validateEmail true when email needs to be required
     * @return \Cake\Validation\Validator
     */
    protected function emailValidator(Validator $validator, bool $validateEmail): Validator
    {
        $this->validateEmail = (bool)$validateEmail;
        $validator
            ->add('email', 'valid_email', ['rule' => 'email'])
            ->notBlank('email', __d('cake_d_c/users', 'This field is required'), function () {
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
    protected function tosValidator(Validator $validator): Validator
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
    public function getRegisterValidators(array $options): Validator
    {
        $validateEmail = isset($options['validate_email']) ? (bool)$options['validate_email'] : null;
        $useTos = $options['use_tos'] ?? null;

        $validator = $this->table->validationRegister(new Validator());
        if ($useTos) {
            $validator = $this->tosValidator($validator);
        }

        if ($validateEmail) {
            $validator = $this->emailValidator($validator, $validateEmail);
        }

        return $validator;
    }

    /**
     * Wrapper for mailer
     *
     * @param \Cake\Datasource\EntityInterface $user user
     * @return void
     */
    protected function sendValidationEmail(EntityInterface $user): void
    {
        $mailer = Configure::read('Users.Email.mailerClass') ?: 'CakeDC/Users.Users';
        $this
            ->getMailer($mailer)
            ->send('validation', [$user]);
    }
}
