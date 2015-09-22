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

use CakeDC\Users\Exception\AccountNotActiveException;
use CakeDC\Users\Exception\MissingEmailException;
use CakeDC\Users\Model\Behavior\Behavior;
use CakeDC\Users\Model\Table\SocialAccountsTable;
use CakeDC\Users\Traits\RandomStringTrait;
use Cake\Datasource\EntityInterface;
use Cake\Utility\Hash;
use DateTime;
use InvalidArgumentException;

/**
 * Covers social features
 *
 */
class SocialBehavior extends Behavior
{
    use RandomStringTrait;

    /**
     * Used to create a default profile link if not available in raw data returned by FB
     */
    const FACEBOOK_SCOPED_ID_URL = "https://www.facebook.com/app_scoped_user_id/";

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
        $existingAccount = $this->_table->SocialAccounts->find()
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
        $existingUser = null;
        if ($useEmail && empty($data->email)) {
            throw new MissingEmailException(__d('Users', 'Email not present'));
        } else {
            $existingUser = $this->_table->find()
                    ->where([$this->_table->alias() . '.email' => $data->email])
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
     */
    protected function _populateUser($data, $existingUser, $useEmail, $validateEmail, $tokenExpiration)
    {
        $accountData['provider'] = $data->provider;
        $accountData['username'] = Hash::get((array)$data->info, 'nickname');
        $accountData['reference'] = $data->uid;
        $accountData['avatar'] = Hash::get((array)$data->info, 'image');
        /* @todo make a pull request to Opauth Facebook Strategy because it does not include link on info array */
        if ($data->provider == SocialAccountsTable::PROVIDER_TWITTER) {
            $accountData['link'] = Hash::get((array)$data->info, 'urls.twitter');
        } elseif ($data->provider == SocialAccountsTable::PROVIDER_FACEBOOK) {
            $accountData['link'] = $this->_getFacebookLink($data->raw);
        }
        $accountData['avatar'] = str_replace('square', 'large', $accountData['avatar']);
        $accountData['description'] = Hash::get((array)$data->info, 'description');
        $accountData['token'] = Hash::get((array)$data->credentials, 'token');
        $accountData['token_secret'] = Hash::get((array)$data->credentials, 'secret');
        $expires = Hash::get((array)$data->credentials, 'expires');
        $accountData['token_expires'] = !empty($expires) ? (new DateTime($expires))->format('Y-m-d H:i:s') : null;
        $accountData['data'] = serialize($data->raw);
        $accountData['active'] = true;

        if (empty($existingUser)) {
            $firstName = Hash::get((array)$data->info, 'first_name');
            $lastName = Hash::get((array)$data->info, 'last_name');
            if (!empty($firstName) && !empty($lastName)) {
                $userData['first_name'] = $firstName;
                $userData['last_name'] = $lastName;
            } else {
                $name = explode(' ', $data->name);
                $userData['first_name'] = Hash::get($name, 0);
                array_shift($name);
                $userData['last_name'] = implode(' ', $name);
            }
            $userData['username'] = Hash::get((array)$data->info, 'nickname');
            $username = Hash::get($userData, 'username');
            if (empty($username)) {
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
            $userData['avatar'] = Hash::get((array)$data->info, 'image');
            $userData['validated'] = $data->validated;
            $userData['tos_date'] = date("Y-m-d H:i:s");
            $userData['gender'] = Hash::get($data->raw, 'gender');
            $userData['timezone'] = Hash::get($data->raw, 'timezone');
            $userData['social_accounts'][] = $accountData;
            $user = $this->_table->newEntity($userData, ['associated' => ['SocialAccounts']]);
            $user = $this->_updateActive($user, false, $tokenExpiration);
        } else {
            if ($useEmail && !$data->validated) {
                $accountData['active'] = false;
            }
            $user = $this->_table->patchEntity($existingUser, [
                'social_accounts' => [$accountData]
            ], ['associated' => ['SocialAccounts']]);
        }
        return $user;
    }

    /**
     * Create a link for facebook profile
     *
     * @param array $raw raw data array returned by Facebook
     * @return string url to facebook profile
     */
    protected function _getFacebookLink($raw = [])
    {
        $link = Hash::get((array)$raw, 'link');
        if (!empty($link)) {
            return $link;
        }

        $id = Hash::get((array)$raw, 'id');
        return self::FACEBOOK_SCOPED_ID_URL . $id;
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
            $existingUsername = $this->_table->find()->where([$this->_table->alias() . '.username' => $username])->count();
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
