<?php
declare(strict_types=1);

namespace CakeDC\Users\Command\Logic;

use Cake\Console\ConsoleIo;
use Cake\ORM\Locator\LocatorAwareTrait;

trait UpdateUserTrait
{
    use LocatorAwareTrait;

    /**
     * Update user by username
     *
     * @param \Cake\Console\ConsoleIo $io The console io
     * @param string $username username
     * @param array $data data
     * @return \CakeDC\Users\Model\Entity\User
     */
    protected function _updateUser(ConsoleIo $io, $username, $data)
    {
        /**
         * @var \CakeDC\Users\Model\Table\UsersTable $UsersTable
         */
        $UsersTable = $this->getTableLocator()->get('Users');
        $user = $UsersTable->find()->where(['username' => $username])->first();
        if (!is_object($user)) {
            $io->abort(__d('cake_d_c/users', 'The user was not found.'));
        }
        /**
         * @var \Cake\Datasource\EntityInterface $user
         */
        $user = $UsersTable->patchEntity($user, $data);
        collection($data)->filter(function ($value, $field) use ($user) {
            return !$user->isAccessible($field);
        })->each(function ($value, $field) use (&$user) {
            $user->{$field} = $value;
        });

        return $UsersTable->saveOrFail($user);
    }
}
