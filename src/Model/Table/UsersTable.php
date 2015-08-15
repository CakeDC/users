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

namespace Users\Model\Table;

use Cake\Datasource\EntityInterface;
use Cake\Network\Email\Email;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use DateTime;
use InvalidArgumentException;
use Users\Exception\AccountNotActiveException;
use Users\Exception\MissingEmailException;
use Users\Exception\TokenExpiredException;
use Users\Exception\UserAlreadyActiveException;
use Users\Exception\UserNotFoundException;
use Users\Exception\WrongPasswordException;
use Users\Model\Entity\User;
use Users\Traits\RandomStringTrait;

/**
 * Users Model
 */
class UsersTable extends Table
{
    use RandomStringTrait;

    /**
     * Flag to set email check in buildRules or not
     *
     * @var bool
     */
    public $isValidateEmail = false;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('users');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->hasMany('SocialAccounts', [
            'foreignKey' => 'user_id',
            'className' => 'Users.SocialAccounts'
        ]);
    }

    /**
     * Adds some rules for password confirm
     * @param Validator $validator Cake validator object.
     * @return Validator
     */
    public function validationPasswordConfirm(Validator $validator)
    {
        $validator
            ->requirePresence('password_confirm', 'create')
            ->notEmpty('password_confirm');

        $validator->add('password', 'custom', [
            'rule' => function ($value, $context) {
                $confirm = Hash::get($context, 'data.password_confirm');
                if (!is_null($confirm) && $value != $confirm) {
                    return false;
                }
                return true;
            },
            'message' => __d('Users', 'Your password does not match your confirm password. Please try again'),
            'on' => ['create', 'update'],
            'allowEmpty' => false
        ]);

        return $validator;
    }

    /**
     * Default validation rules.
     *
     * @param Validator $validator Validator instance.
     * @return Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('username', 'create')
            ->notEmpty('username');

        $validator
            ->requirePresence('password', 'create')
            ->notEmpty('password');

        $validator
            ->allowEmpty('first_name');

        $validator
            ->allowEmpty('last_name');

        $validator
            ->allowEmpty('token');

        $validator
            ->add('token_expires', 'valid', ['rule' => 'datetime'])
            ->allowEmpty('token_expires');

        $validator
            ->allowEmpty('api_token');

        $validator
            ->add('activation_date', 'valid', ['rule' => 'datetime'])
            ->allowEmpty('activation_date');

        $validator
            ->add('tos_date', 'valid', ['rule' => 'datetime'])
            ->allowEmpty('tos_date');

        return $validator;
    }

    /**
     * Default+Email validation rules.
     *
     * @param Validator $validator Validator instance.
     * @return Validator
     */
    public function validationEmail(Validator $validator)
    {
        $validator = $this->validationRegister($validator);
        $validator
                ->add('email', 'valid', ['rule' => 'email'])
                ->requirePresence('email', 'create')
                ->notEmpty('email');
        return $validator;
    }

    /**
     * Wrapper for all validation rules for register
     * @param Validator $validator Cake validator object.
     *
     * @return Validator
     */
    public function validationRegister(Validator $validator)
    {
        $validator = $this->validationDefault($validator);
        $validator = $this->validationPasswordConfirm($validator);
        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param RulesChecker $rules The rules object to be modified.
     * @return RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['username']), [
            'errorField' => 'username',
            'message' => __d('Users', 'Username already exists')
        ]);

        if ($this->isValidateEmail) {
            $rules->add($rules->isUnique(['email']), [
                'errorField' => 'email',
                'message' => __d('Users', 'Email already exists')
            ]);
        }

        return $rules;
    }

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
            $this->_updateToken($user, $tokenExpiration);
        } else {
            $user['active'] = true;
            $user['activation_date'] = new DateTime();
        }
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
     * Removes user token for validation
     *
     * @param User $user user object.
     * @return User
     *
     * @todo: move into new behavior
     */
    protected function _removesValidationToken(User $user)
    {
        $user->token = null;
        $user->token_expires = null;
        $result = $this->save($user);

        return $result;
    }

    /**
     * Activates an user
     *
     * @param User $user user object.
     * @return mixed User entity or bool false if the user could not be activated
     * @throws UserAlreadyActiveException
     * @todo: move into new behavior
     */
    public function activateUser(User $user)
    {
        if ($user->active) {
            throw new UserAlreadyActiveException("User account already validated");
        }
        $user = $this->_removesValidationToken($user);
        $user->activation_date = new DateTime();
        $user->active = true;
        $result = $this->save($user);

        return $result;
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
     * @todo: move into new behavior
     */
    public function resetToken($reference, array $options = [])
    {
        if (empty($reference)) {
            throw new InvalidArgumentException(sprintf('Reference cannot be null'));
        }

        if (empty(Hash::get($options, 'expiration'))) {
            throw new InvalidArgumentException(sprintf('Token expiration cannot be null'));
        }

        $user = $this->findAllByUsernameOrEmail($reference, $reference)->first();

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
        $this->_updateToken($user, Hash::get($options, 'expiration'));
        $saveResult = $this->save($user);
        if (Hash::get($options, 'sendEmail')) {
            $this->sendResetPasswordEmail($saveResult);
        }
        return $saveResult;
    }

    /**
     * Generate token_expires and token in a user
     * @param type $user Reference to user.
     * @param string $tokenExpiration new token_expires user.
     *
     * @return type
     */
    protected function _updateToken(&$user, $tokenExpiration)
    {
        $expires = new DateTime();
        $expires->modify(__d('Users', '+ {0} secs', $tokenExpiration));
        $user['token_expires'] = $expires;
        $user['token'] = $this->randomString();

        return $user;
    }

    /**
     * Checks if username exists and generate a new one
     * @param string $username username data.
     * @return string
     *
     * @todo: move into new behavior
     */
    public function generateUniqueUsername($username)
    {
        $i = 0;
        $checkUsername = $username;
        while (true) {
            $existingUsername = $this->find()->where([$this->alias() . '.username' => $checkUsername])->count();
            if ($existingUsername > 0) {
                $checkUsername = $username . $i;
                $i++;
                continue;
            }
            break;
        }
        return $checkUsername;
    }

    /**
     * Performs social login
     * @param array $data Array social login.
     * @param array $options Array option data.
     * @return bool|EntityInterface|mixed
     *
     * @todo: move into new behavior
     * @todo: Improve docblock
     */
    public function socialLogin($data, $options = [])
    {
        $provider = $data->provider;
        $reference = $data->uid;
        $existingAccount = $this->SocialAccounts->find()
                ->where(['SocialAccounts.reference' => $reference, 'SocialAccounts.provider' => $provider])
                ->contain(['Users'])
                ->first();
        if (empty($existingAccount->user)) {
            $user = $this->_createSocialUser($data, $options);
            if (!empty($user->social_accounts[0])) {
                $existingAccount = $user->social_accounts[0];
            } else {
                //@todo: what if we don't have a social account after createSocialUser?
                throw new InvalidArgumentException(__d('Users', 'Unable to login user with reference {0}', $reference));
            }
        } else {
            $user = $existingAccount->user;
        }
        if (!empty($existingAccount)) {
            if ($existingAccount->active) {
                return $user;
            } else {
                throw new AccountNotActiveException([
                    $existingAccount->provider,
                    $existingAccount->reference
                ]);
            }
        }
        return false;
    }

    /**
     * Creates social user
     * @param array $data Array social user.
     * @param array $options Array option data.
     * @return bool|EntityInterface|mixed
     *
     * @todo: move into new behavior
     * @todo: Improve docblock
     */
    protected function _createSocialUser($data, $options = [])
    {
        $useEmail = Hash::get($options, 'use_email');
        $validateEmail = Hash::get($options, 'validate_email');
        $tokenExpiration = Hash::get($options, 'token_expiration');
        if ($useEmail && empty($data->email)) {
            throw new MissingEmailException(__d('Users', 'Email not present'));
        } else {
            $existingUser = $this->find()
                    ->where([$this->alias() . '.email' => $data->email])
                    ->first();
        }
        $user = $this->_populateUser($data, $existingUser, $useEmail, $validateEmail, $tokenExpiration);
        $this->isValidateEmail = $validateEmail;
        $result = $this->save($user);
        return $result;
    }

    /**
     * @param array $data Array social login.
     * @param string $existingUser user data.
     * @param string $useEmail email to use.
     * @param string $validateEmail email to validate.
     * @param string $tokenExpiration token_expires data.
     * @return EntityInterface|\Cake\ORM\Entity
     */
    protected function _populateUser($data, $existingUser, $useEmail, $validateEmail, $tokenExpiration)
    {
        $accountData['provider'] = $data->provider;
        $accountData['username'] = Hash::get($data->info, 'nickname');
        $accountData['reference'] = $data->uid;
        $accountData['avatar'] = Hash::get($data->info, 'image');
        /* @todo make a pull request to Opauth Facebook Strategy because it does not include link on info array */
        if ($data->provider == SocialAccountsTable::PROVIDER_TWITTER) {
            $accountData['link'] = Hash::get($data->info, 'urls.twitter');
        } elseif ($data->provider == SocialAccountsTable::PROVIDER_FACEBOOK) {
            $accountData['link'] = Hash::get($data->raw, 'link');
        }
        $accountData['avatar'] = str_replace('square', 'large', $accountData['avatar']);
        $accountData['description'] = Hash::get($data->info, 'description');
        $accountData['token'] = Hash::get((array)$data->credentials, 'token');
        $accountData['token_secret'] = Hash::get((array)$data->credentials, 'secret');
        $accountData['token_expires'] = !empty(Hash::get((array)$data->credentials, 'expires')) ? (new DateTime(Hash::get((array)$data->credentials, 'expires')))->format('Y-m-d H:i:s') : null;
        $accountData['data'] = serialize($data->raw);
        $accountData['active'] = true;

        if (empty($existingUser)) {
            if (!empty($data->info['first_name']) && !empty($data->info['last_name'])) {
                $userData['first_name'] = Hash::get($data->info, 'first_name');
                $userData['last_name'] = Hash::get($data->info, 'last_name');
            } else {
                $name = explode(' ', $data->name);
                $userData['first_name'] = Hash::get($name, 0);
                array_shift($name);
                $userData['last_name'] = implode(' ', $name);
            }
            $userData['username'] = Hash::get($data->info, 'nickname');
            if (empty(Hash::get($userData, 'username'))) {
                if (!empty($data->email)) {
                    $email = explode('@', $data->email);
                    $userData['username'] = Hash::get($email, 0);
                } else {
                    $firstName = Hash::get($userData, 'first_name');
                    $lastName = Hash::get($userData, 'last_name');
                    $userData['username'] = strtolower($firstName . $lastName);
                    $userData['username'] = preg_replace('/[^A-Za-z0-9]/i', '', Hash::get($userData, 'username'));
                }

            }
            $userData['username'] = $this->generateUniqueUsername(Hash::get($userData, 'username'));
            if ($useEmail) {
                $userData['email'] = $data->email;
                if (!$data->validated) {
                    $accountData['active'] = false;
                }
            }
            $userData['password'] = $this->randomString();
            $userData['avatar'] = Hash::get($data->info, 'image');
            $userData['validated'] = $data->validated;
            $this->_updateActive($userData, false, $tokenExpiration);
            $userData['tos_date'] = date("Y-m-d H:i:s");
            $userData['gender'] = Hash::get($data->raw, 'gender');
            $userData['timezone'] = Hash::get($data->raw, 'timezone');
            $userData['social_accounts'][] = $accountData;
            $user = $this->newEntity($userData, ['associated' => ['SocialAccounts']]);
        } else {
            if ($useEmail && !$data->validated) {
                $accountData['active'] = false;
            }
            $user = $this->patchEntity($existingUser, [
                'social_accounts' => [$accountData]
            ], ['associated' => ['SocialAccounts']]);
        }
        return $user;
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
        return $this->getEmailInstance($email)
                ->template('Users.reset_password')
                ->to($user['email'])
                ->subject($subject)
                ->viewVars($user->toArray())
                ->send();
    }

    /**
     * Change password method
     *
     * @param User $user user data.
     * @return mixed
     * @internal param $data
     */
    public function changePassword($user)
    {
        $currentUser = $this->get($user->id, [
            'contain' => []
        ]);

        if (!empty($user->current_password)) {
            if (!$user->checkPassword($user->current_password, $currentUser->password)) {
                throw new WrongPasswordException(__d('Users', 'The old password does not match'));
            }
        }
        $user = $this->save($user);
        if (!empty($user)) {
            $user = $this->_removesValidationToken($user);
        }
        return $user;
    }

    /**
     * Get or initialize the email instance. Used for mocking.
     *
     * @param Email $email if email provided, we'll use the instance instead of creating a new one
     * @return Email
     */
    public function getEmailInstance(Email $email = null)
    {
        if ($email === null) {
            $email = new Email('default');
            $email->template('Users.validation')
                    ->emailFormat('both');
        }

        return $email;
    }
}
