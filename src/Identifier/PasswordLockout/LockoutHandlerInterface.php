<?php

namespace CakeDC\Users\Identifier\PasswordLockout;

interface LockoutHandlerInterface
{
    /**
     * @param string|int $id User's id
     * @return bool
     */
    public function isUnlocked(string|int $id): bool;

    /**
     * @param string|int $id User's id
     *
     * @return void
     */
    public function newFail(string|int $id): void;
}
