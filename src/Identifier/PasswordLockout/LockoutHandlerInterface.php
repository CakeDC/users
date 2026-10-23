<?php
declare(strict_types=1);

namespace CakeDC\Users\Identifier\PasswordLockout;

use ArrayAccess;

interface LockoutHandlerInterface
{
    /**
     * @param \ArrayAccess|array $identity
     * @return bool
     */
    public function isUnlocked(ArrayAccess|array $identity): bool;

    /**
     * @param string|int $id User's id
     * @return void
     */
    public function newFail(string|int $id): void;
}
