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
use Cake\Utility\Hash;
use DateTime;
use InvalidArgumentException;
use Users\Exception\AccountNotActiveException;
use Users\Exception\MissingEmailException;
use Users\Model\Table\SocialAccountsTable;

/**
 * Covers social features
 *
 */
trait SocialTrait
{
    /**
     * Performs social login
     *
     * @param array $data Array social login.
     * @param array $options Array option data.
     * @return bool|EntityInterface|mixed
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
     * Creates social user, populate the user data based on the social login data first and save it
     *
     * @param array $data Array social user.
     * @param array $options Array option data.
     * @return bool|EntityInterface|mixed result of the save operation
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
     * Build new user entity either by using an existing user or extracting the data from the social login
     * data to create a new one
     *
     * @param array $data Array social login.
     * @param EntityInterface $existingUser user data.
     * @param string $useEmail email to use.
     * @param string $validateEmail email to validate.
     * @param string $tokenExpiration token_expires data.
     * @return EntityInterface
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
            $userData['username'] = $this->_generateUsername(Hash::get($userData, 'username'));
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
     * Checks if username exists and generate a new one
     *
     * @param string $username username data.
     * @return string
     */
    protected function _generateUsername($username)
    {
        $i = 0;
        while (true) {
            $existingUsername = $this->find()->where([$this->alias() . '.username' => $username])->count();
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
