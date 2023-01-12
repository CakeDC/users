<?php
declare(strict_types=1);

namespace CakeDC\Users\Command\Logic;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;

trait ChangeUserActiveTrait
{
    use UpdateUserTrait;

    /**
     * Change user active field
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @param bool $active active value
     * @return \CakeDC\Users\Model\Entity\User
     */
    protected function _changeUserActive(Arguments $args, ConsoleIo $io, $active)
    {
        $username = $args->getArgumentAt(0);
        if (empty($username)) {
            $io->abort(__d('cake_d_c/users', 'Please enter a username.'));
        }
        $data = [
            'active' => $active,
        ];

        return $this->_updateUser($io, $username, $data);
    }
}
