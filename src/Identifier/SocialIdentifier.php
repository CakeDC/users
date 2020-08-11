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

namespace CakeDC\Users\Identifier;

use Authentication\Identifier\AbstractIdentifier;
use Cake\Core\Configure;
use Cake\Event\EventDispatcherTrait;
use Cake\ORM\Locator\LocatorAwareTrait;
use CakeDC\Users\Plugin;

class SocialIdentifier extends AbstractIdentifier
{
    use EventDispatcherTrait;
    use LocatorAwareTrait;

    public const CREDENTIAL_KEY = 'socialAuthUser';

    /**
     * Default configuration.
     * - `usersTable` name of usersTable to use:
     * - `resolver` The resolver implementation to use.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'authFinder' => 'active',
    ];

    /**
     * Identifies an user or service by the passed credentials
     *
     * @param array $credentials Authentication credentials
     * @return \ArrayAccess|array|null
     */
    public function identify(array $credentials)
    {
        if (!isset($credentials[self::CREDENTIAL_KEY])) {
            return null;
        }

        $user = $this->createOrGetUser($credentials[self::CREDENTIAL_KEY]);

        if (!$user) {
            return null;
        }

        if ($user->get('social_accounts')) {
            $this->dispatchEvent(Plugin::EVENT_AFTER_REGISTER, compact('user'));
        }

        $user = $this->findUser($user)->firstOrFail();

        return $user;
    }

    /**
     * Get query object for fetching user from database.
     *
     * @param \Cake\Datasource\EntityInterface $user The user.
     * @return \Cake\ORM\Query
     */
    protected function findUser($user)
    {
        $table = $this->getUsersTable();
        $finder = $this->getConfig('authFinder');

        $primaryKey = (array)$table->getPrimaryKey();

        $conditions = [];
        foreach ($primaryKey as $key) {
            $conditions[$table->aliasField($key)] = $user->get($key);
        }

        return $table->find($finder)->where($conditions);
    }

    /**
     * Create a new user or get if exists one for the social data
     *
     * @param mixed $data social data
     * @return mixed
     */
    protected function createOrGetUser($data)
    {
        $options = [
            'use_email' => Configure::read('Users.Email.required'),
            'validate_email' => Configure::read('Users.Email.validate'),
            'token_expiration' => Configure::read('Users.Token.expiration'),
        ];

        return $this->getUsersTable()->socialLogin($data, $options);
    }

    /**
     * Get users table based on internal config (usersTable) or users config (Users.table)
     *
     * @return \Cake\ORM\Table
     */
    protected function getUsersTable()
    {
        $userModel = $this->getConfig('usersTable', Configure::read('Users.table'));

        return $this->getTableLocator()->get($userModel);
    }
}
