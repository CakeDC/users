<?php
namespace CakeDC\Users\Social\Locator;

use CakeDC\Users\Auth\Exception\InvalidSettingsException;
use CakeDC\Users\Model\Entity\User;
use CakeDC\Users\Plugin;
use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventDispatcherTrait;
use Cake\ORM\TableRegistry;

class DatabaseLocator implements LocatorInterface
{
    use EventDispatcherTrait;
    use InstanceConfigTrait;

    const ERROR_MISSING_EMAIL = 10;
    const ERROR_ACCOUNT_NOT_ACTIVE = 20;
    const ERROR_USER_NOT_ACTIVE = 30;
    const ERROR_INVALID_RECAPTCHA = 40;

    protected $_defaultConfig = [
        'finder' => 'all',
    ];

    /**
     * DatabaseLocator constructor.
     *
     * @param array $config optional config
     */
    public function __construct(array $config = [])
    {
        $config += ['userModel' => Configure::read('Users.table')];
        $this->setConfig($config);
    }

    /**
     * Get or create the user based on the $rawData
     *
     * @param array $rawData mapped social user data
     * @return User
     */
    public function getOrCreate(array $rawData): User
    {
        if (!$this->getConfig('userModel')) {
            throw new InvalidSettingsException(__d('CakeDC/Users', 'Users table is not defined'));
        }

        $user = $this->_socialLogin($rawData);

        if (!$user) {
            throw new RecordNotFoundException(__d('CakeDC/Users', 'Could not locate user'));
        }
        // If new SocialAccount was created $user is returned containing it
        if ($user->get('social_accounts')) {
            $this->dispatchEvent(Plugin::EVENT_AFTER_SOCIAL_REGISTER, compact('user'));
        }

        $user = $this->findUser($user)->firstOrFail();

        return $user;
    }

    /**
     * Get query object for fetching user from database.
     *
     * @param User $user The user.
     *
     * @return \Cake\Orm\Query
     */
    protected function findUser($user)
    {
        $userModel = $this->getConfig('userModel');
        $table = TableRegistry::getTableLocator()->get($userModel);
        $finder = $this->getConfig('finder');

        $primaryKey = (array)$table->getPrimaryKey();

        $conditions = [];
        foreach ($primaryKey as $key) {
            $conditions[$table->aliasField($key)] = $user->get($key);
        }

        return $table->find($finder)->where($conditions);
    }

    /**
     * @param mixed $data data
     * @return mixed
     */
    protected function _socialLogin($data)
    {
        $options = [
            'use_email' => Configure::read('Users.Email.required'),
            'validate_email' => Configure::read('Users.Email.validate'),
            'token_expiration' => Configure::read('Users.Token.expiration')
        ];

        $userModel = Configure::read('Users.table');
        $User = TableRegistry::getTableLocator()->get($userModel);
        $user = $User->socialLogin($data, $options);

        return $user;
    }
}
