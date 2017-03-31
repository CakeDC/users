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

use CakeDC\Users\Exception\AccountNotActiveException;
use CakeDC\Users\Exception\MissingEmailException;
use CakeDC\Users\Exception\UserNotActiveException;
use CakeDC\Users\Traits\RandomStringTrait;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventDispatcherTrait;
use Cake\Utility\Hash;
use DateTime;
use InvalidArgumentException;

/**
 * Covers social features
 *
 */
class SocialBehavior extends BaseTokenBehavior
{
    use EventDispatcherTrait;
    use RandomStringTrait;

    /**
     * Performs social login
     *
     * @param array $data Array social login.
     * @param array $options Array option data.
     * @throws InvalidArgumentException
     * @throws UserNotActiveException
     * @throws AccountNotActiveException
     * @return bool|EntityInterface|mixed
     */
    public function socialLogin(array $data, array $options)
    {
        $reference = Hash::get($data, 'id');
        $existingAccount = $this->_table->SocialAccounts->find()
                ->where([
                    'SocialAccounts.reference' => $reference,
                    'SocialAccounts.provider' => Hash::get($data, 'provider')
                ])
                ->contain(['Users'])
                ->first();
        if (empty($existingAccount->user)) {
            $user = $this->_createSocialUser($data, $options);
            if (!empty($user->social_accounts[0])) {
                $existingAccount = $user->social_accounts[0];
            } else {
                //@todo: what if we don't have a social account after createSocialUser?
                throw new InvalidArgumentException(__d('CakeDC/Users', 'Unable to login user with reference {0}', $reference));
            }
        } else {
            $user = $existingAccount->user;
        }
        if (!empty($existingAccount)) {
            if ($existingAccount->active) {
                if ($user->active) {
                    return $user;
                } else {
                    throw new UserNotActiveException([
                        $existingAccount->provider,
                        $existingAccount->$user
                    ]);
                }
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
     * Creates social user, populate the user data based on the social login data first and save it
     *
     * @param array $data Array social user.
     * @param array $options Array option data.
     * @throws MissingEmailException
     * @return bool|EntityInterface|mixed result of the save operation
     */
    protected function _createSocialUser($data, $options = [])
    {
        $useEmail = Hash::get($options, 'use_email');
        $validateEmail = Hash::get($options, 'validate_email');
        $tokenExpiration = Hash::get($options, 'token_expiration');
        $existingUser = null;
        $email = Hash::get($data, 'email');
        if ($useEmail && empty($email)) {
            throw new MissingEmailException(__d('CakeDC/Users', 'Email not present'));
        } else {
            $existingUser = $this->_table->find()
                    ->where([$this->_table->aliasField('email') => $email])
                    ->first();
        }

        $user = $this->_populateUser($data, $existingUser, $useEmail, $validateEmail, $tokenExpiration);
        $this->_table->isValidateEmail = $validateEmail;
        $result = $this->_table->save($user);

        return $result;
    }

    /**
     * Build new user entity either by using an existing user or extracting the data from the social login
     * data to create a new one
     *
     * @param array $data Array social login.
     * @param EntityInterface $existingUser user data.
     * @param string $useEmail email to use.
     * @param string $validateEmail email to validate.
     * @param string $tokenExpiration token_expires data.
     * @return EntityInterface
     * @todo refactor
     */
    protected function _populateUser($data, $existingUser, $useEmail, $validateEmail, $tokenExpiration)
    {
        $accountData['username'] = Hash::get($data, 'username');
        $accountData['reference'] = Hash::get($data, 'id');
        $accountData['avatar'] = Hash::get($data, 'avatar');
        $accountData['link'] = Hash::get($data, 'link');

        $accountData['avatar'] = str_replace('normal', 'square', $accountData['avatar']);
        $accountData['description'] = Hash::get($data, 'bio');
        $accountData['token'] = Hash::get($data, 'credentials.token');
        $accountData['token_secret'] = Hash::get($data, 'credentials.secret');
        $expires = Hash::get($data, 'credentials.expires');
        if (!empty($expires)) {
            $expiresTime = new DateTime();
            $accountData['token_expires'] = $expiresTime->setTimestamp($expires)->format('Y-m-d H:i:s');
        } else {
            $accountData['token_expires'] = null;
        }
        $accountData['data'] = serialize(Hash::get($data, 'raw'));
        $accountData['active'] = true;

        $dataValidated = Hash::get($data, 'validated');

        if (empty($existingUser)) {
            $firstName = Hash::get($data, 'first_name');
            $lastName = Hash::get($data, 'last_name');
            if (!empty($firstName) && !empty($lastName)) {
                $userData['first_name'] = $firstName;
                $userData['last_name'] = $lastName;
            } else {
                $name = explode(' ', Hash::get($data, 'full_name'));
                $userData['first_name'] = Hash::get($name, 0);
                array_shift($name);
                $userData['last_name'] = implode(' ', $name);
            }
            $userData['username'] = Hash::get($data, 'username');
            $username = Hash::get($userData, 'username');
            if (empty($username)) {
                $dataEmail = Hash::get($data, 'email');
                if (!empty($dataEmail)) {
                    $email = explode('@', $dataEmail);
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
                $userData['email'] = Hash::get($data, 'email');
                if (empty($dataValidated)) {
                    $accountData['active'] = false;
                }
            }

            $userData['password'] = $this->randomString();
            $userData['avatar'] = Hash::get($data, 'avatar');
            $userData['validated'] = !empty($dataValidated);
            $userData['tos_date'] = date("Y-m-d H:i:s");
            $userData['gender'] = Hash::get($data, 'gender');
            $userData['social_accounts'][] = $accountData;

            $user = $this->_table->newEntity($userData);
            $user = $this->_updateActive($user, false, $tokenExpiration);
        } else {
            if ($useEmail && empty($dataValidated)) {
                $accountData['active'] = false;
            }
            $user = $existingUser;
        }
        $socialAccount = $this->_table->SocialAccounts->newEntity($accountData);
        //ensure provider is present in Entity
        $socialAccount['provider'] = Hash::get($data, 'provider');
        $user['social_accounts'] = [$socialAccount];

        return $user;
    }

    /**
     * Checks if username exists and generate a new one
     *
     * @param string $username username data.
     * @return string
     */
    public function generateUniqueUsername($username)
    {
        $i = 0;
        while (true) {
            $existingUsername = $this->_table->find()
                ->where([$this->_table->aliasField('username') => $username])
                ->count();
            if ($existingUsername > 0) {
                $username = $username . $i;
                $i++;
                continue;
            }
            break;
        }

        return $username;
    }
}
