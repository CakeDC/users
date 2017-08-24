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

use Cake\Datasource\EntityInterface;
use Cake\I18n\Time;
use Cake\ORM\Behavior;
use Cake\Utility\Hash;

/**
 * LinkSocial behavior
 */
class LinkSocialBehavior extends Behavior
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * Link an user account with a social account (facebook, google)
     *
     * @param EntityInterface  $user User to link.
     * @param array $data Social account information.
     *
     * @return EntityInterface
     */
    public function linkSocialAccount(EntityInterface $user, $data)
    {
        $reference = Hash::get($data, 'id');
        $alias = $this->_table->SocialAccounts->getAlias();
        $socialAccount = $this->_table->SocialAccounts->find()
            ->where([
                $alias . '.reference' => $reference,
                $alias . '.provider' => Hash::get($data, 'provider')
            ])->first();

        if ($socialAccount && $user->id !== $socialAccount->user_id) {
            $user->errors([
                'social_accounts' => [
                    '_existsIn' => __d('CakeDC/Users', 'Social account already associated to another user')
                ]
            ]);

            return $user;
        }

        return $this->createOrUpdateSocialAccount($user, $data, $socialAccount);
    }

    /**
     * Create or update a new social account linking to the user.
     *
     * @param EntityInterface  $user User to link.
     * @param array $data Social account information.
     * @param EntityInterface $socialAccount to update or create.
     *
     * @return EntityInterface
     */
    protected function createOrUpdateSocialAccount(EntityInterface $user, $data, $socialAccount)
    {
        if (!$socialAccount) {
            $socialAccount = $this->_table->SocialAccounts->newEntity();
        }

        $data['user_id'] = $user->id;
        $socialAccount = $this->populateSocialAccount($socialAccount, $data);

        $result = $this->_table->SocialAccounts->save($socialAccount);

        $accounts = (array)$user->social_accounts;
        $found = false;
        foreach ($accounts as $key => $account) {
            if ($account->id == $socialAccount->id) {
                $accounts[$key] = $socialAccount;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $accounts[] = $socialAccount;
        }
        $user->social_accounts = $accounts;

        if ($result && !$result->errors()) {
            return $user;
        }

        return $user;
    }

    /**
     * Populate the social account
     *
     * @param EntityInterface $socialAccount to populate.
     * @param array $data Social account information.
     *
     * @return EntityInterface
     */
    protected function populateSocialAccount($socialAccount, $data)
    {
        $accountData = $socialAccount->toArray();
        $accountData['username'] = Hash::get($data, 'username');
        $accountData['reference'] = Hash::get($data, 'id');
        $accountData['avatar'] = Hash::get($data, 'avatar');
        $accountData['link'] = Hash::get($data, 'link');
        $accountData['avatar'] = str_replace('normal', 'square', $accountData['avatar']);
        $accountData['description'] = Hash::get($data, 'bio');
        $accountData['token'] = Hash::get($data, 'credentials.token');
        $accountData['token_secret'] = Hash::get($data, 'credentials.secret');
        $accountData['user_id'] = Hash::get($data, 'user_id');
        $accountData['token_expires'] = null;
        $expires = Hash::get($data, 'credentials.expires');
        if (!empty($expires)) {
            $expiresTime = new Time();
            $accountData['token_expires'] = $expiresTime->setTimestamp($expires)->format('Y-m-d H:i:s');
        }

        $accountData['data'] = serialize(Hash::get($data, 'raw'));
        $accountData['active'] = true;

        $socialAccount = $this->_table->SocialAccounts->patchEntity($socialAccount, $accountData);
        //ensure provider is present in Entity
        $socialAccount['provider'] = Hash::get($data, 'provider');

        return $socialAccount;
    }
}
