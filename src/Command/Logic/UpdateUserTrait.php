<?php
declare(strict_types=1);

namespace CakeDC\Users\Command\Logic;

use Cake\ORM\Locator\LocatorAwareTrait;
use CakeDC\Users\Model\Entity\User;

trait UpdateUserTrait
{
    use LocatorAwareTrait;

    /**
     * Update user by username
     *
     * @param string $username username
     * @param array $data data
     * @return \CakeDC\Users\Model\Entity\User
     */
    protected function updateUser(string $username, array $data): User
    {
        /**
         * @var \CakeDC\Users\Model\Table\UsersTable $UsersTable
         */
        $UsersTable = $this->getTableLocator()->get('Users');
        $user = $UsersTable->find()->where(['username' => $username])->first();
        if (!is_object($user)) {
            $this->io->abort(__d('cake_d_c/users', 'The user was not found.'));
        }
        /**
         * @var \Cake\Datasource\EntityInterface $user
         */
        $user = $UsersTable->patchEntity($user, $data);
        collection($data)->filter(function ($value, $field) use ($user) {
            return !$user->isPatchable($field);
        })->each(function ($value, $field) use (&$user): void {
            $user->set($field, $value);
        });

        return $UsersTable->saveOrFail($user);
    }
}
