<?php

declare(strict_types=1);

namespace CakeDC\Users;

/**
 * @deprecated 5.3.0 This class will be removed in a future version of CakePHP
 * Use \CakeDC\Users\UsersPlugin instead.
 */
class Plugin extends UsersPlugin
{
    /**
     * @param array $config Plugin configuration.
     */
    public function __construct(array $config = [])
    {
        deprecationWarning(
            '5.3.0',
            'The `Plugin` class is deprecated. Use `\CakeDC\Users\UsersPlugin` instead ' .
                'to comply with CakePHP 5.3+ naming conventions.'
        );
        parent::__construct($config);
    }
}
