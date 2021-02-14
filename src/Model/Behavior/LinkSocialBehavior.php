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

use Cake\Datasource\EntityInterface;
use Cake\I18n\Time;
use Cake\ORM\Behavior;

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
     * @param \Cake\Datasource\EntityInterface $user User to link.
     * @param array $data Social account information.
     * @return \Cake\Datasource\EntityInterface
     */
    public function linkSocialAccount(EntityInterface $user, $data)
    {
        $reference = $data['id'] ?? null;
        $alias = $this->_table->SocialAccounts->getAlias();
        $socialAccount = $this->_table->SocialAccounts->find()
            ->where([
                $alias . '.reference' => $reference,
                $alias . '.provider' => $data['provider'] ?? null,
            ])->first();

        if ($socialAccount && $user->id !== $socialAccount->user_id) {
            $user->setErrors([
                'social_accounts' => [
                    '_existsIn' => __d('cake_d_c/users', 'Social account already associated to another user'),
                ],
            ]);

            return $user;
        }

        return $this->createOrUpdateSocialAccount($user, $data, $socialAccount);
    }

    /**
     * Create or update a new social account linking to the user.
     *
     * @param \Cake\Datasource\EntityInterface $user User to link.
     * @param array $data Social account information.
     * @param \Cake\Datasource\EntityInterface $socialAccount to update or create.
     * @return \Cake\Datasource\EntityInterface
     */
    protected function createOrUpdateSocialAccount(EntityInterface $user, $data, $socialAccount)
    {
        if (!$socialAccount) {
            $socialAccount = $this->_table->SocialAccounts->newEntity([]);
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

        if ($result && !$result->getErrors()) {
            return $user;
        }

        return $user;
    }

    /**
     * Populate the social account
     *
     * @param \Cake\Datasource\EntityInterface $socialAccount to populate.
     * @param array $data Social account information.
     * @return \Cake\Datasource\EntityInterface
     */
    protected function populateSocialAccount($socialAccount, $data)
    {
        $accountData = $socialAccount->toArray();
        $accountData['username'] = $data['username'] ?? null;
        $accountData['reference'] = $data['id'] ?? null;
        $accountData['avatar'] = $data['avatar'] ?? null;
        $accountData['link'] = $data['link'] ?? null;
        if ($accountData['avatar'] ?? null) {
            $accountData['avatar'] = str_replace('normal', 'square', $accountData['avatar']);
        }
        $accountData['description'] = $data['bio'] ?? null;
        $accountData['token'] = $data['credentials']['token'] ?? null;
        $accountData['token_secret'] = $data['credentials']['secret'] ?? null;
        $accountData['user_id'] = $data['user_id'] ?? null;
        $accountData['token_expires'] = null;
        $expires = $data['credentials']['expires'] ?? null;
        if (!empty($expires)) {
            $expiresTime = new Time();
            $accountData['token_expires'] = $expiresTime->setTimestamp($expires)->format('Y-m-d H:i:s');
        }

        $accountData['data'] = serialize($data['raw'] ?? null);
        $accountData['active'] = true;

        $socialAccount = $this->_table->SocialAccounts->patchEntity($socialAccount, $accountData);
        //ensure provider is present in Entity
        $socialAccount['provider'] = $data['provider'] ?? null;

        return $socialAccount;
    }
}
