<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2024, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2024, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Identifier;

use ArrayAccess;
use Authentication\Identifier\PasswordIdentifier;
use CakeDC\Users\Identifier\PasswordLockout\LockoutHandler;
use CakeDC\Users\Identifier\PasswordLockout\LockoutHandlerInterface;

class PasswordLockoutIdentifier extends PasswordIdentifier
{
    /**
     * @var \CakeDC\Users\Identifier\PasswordLockout\LockoutHandlerInterface|null
     */
    protected ?LockoutHandlerInterface $lockoutHandler = null;

    public function __construct(array $config = [])
    {
        $this->_defaultConfig['lockoutHandler'] = [
            'className' => LockoutHandler::class,
        ];

        parent::__construct($config);
    }


    /**
     * @inheritDoc
     */
    protected function _checkPassword(ArrayAccess|array|null $identity, ?string $password): bool
    {
        $check = parent::_checkPassword($identity, $password);
        $handler = $this->getLockoutHandler();
        if (!$check) {
            $handler->newFail($identity['id']);

            return false;
        }

        return $handler->isUnlocked($identity['id']);
    }

    /**
     * @return \CakeDC\Users\Identifier\PasswordLockout\LockoutHandler
     */
    protected function getLockoutHandler(): LockoutHandler
    {
        if ($this->lockoutHandler !== null) {
            return $this->lockoutHandler;
        }
        $config = $this->getConfig('lockoutHandler');
        if ($config !== null) {
            $this->lockoutHandler = $this->buildLockoutHandler($config);

            return $this->lockoutHandler;
        }
        throw new \RuntimeException(__d('cake_d_c/users', 'Lockout handler has not been set.'));
    }

    /**
     * @param array|string $config
     * @return \CakeDC\Users\Identifier\PasswordLockout\LockoutHandlerInterface
     */
    protected function buildLockoutHandler(array|string $config): LockoutHandlerInterface
    {
        if (is_string($config)) {
            $config = [
                'className' => $config,
            ];
        }
        if (!isset($config['className'])) {
            throw new \InvalidArgumentException(__d('cake_d_c/users', 'Option `className` for lockout handler is not present.'));
        }
        $className = $config['className'];

        return new $className($config);
    }
}
