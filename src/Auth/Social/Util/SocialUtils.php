<?php
/**
 * Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Auth\Social\Util;

use League\OAuth2\Client\Provider\AbstractProvider;
use ReflectionClass;

/**
 * Social Utils
 *
 */
class SocialUtils
{
    /**
     * Get provider from classname
     *
     * @param AbstractProvider $provider provider
     * @return string
     */
    public static function getProvider(AbstractProvider $provider)
    {
        $reflect = new ReflectionClass($provider);

        return $reflect->getShortName();
    }
}
