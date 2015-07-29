<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Users\Auth\Factory;

use Opauth\Opauth\Opauth;

/**
 * Class OpauthFactory
 * @package Users\Auth\Factory
 */
class OpauthFactory
{

    /**
     * Creates an Opauth instance
     *
     * @param array $config User configuration
     * @param bool $run Whether Opauth should auto run after initialization.
     *
     * @return Opauth
     */
    public static function create($config = null, $run = null)
    {
        return new Opauth($config, $run);
    }
}
