<?php
declare(strict_types=1);

namespace CakeDC\Users\Command\Logic;

use CakeDC\Users\Model\Entity\User;

trait ChangeUserActiveTrait
{
    use UpdateUserTrait;

    /**
     * Change user active field
     *
     * @param bool $active active value
     * @return \CakeDC\Users\Model\Entity\User
     */
    protected function changeUserActive(bool $active): User
    {
        $username = $this->args->getArgumentAt(0);
        if (empty($username)) {
            $this->io->abort(__d('cake_d_c/users', 'Please enter a username.'));
        }
        $data = [
            'active' => $active,
        ];

        return $this->updateUser($username, $data);
    }
}
