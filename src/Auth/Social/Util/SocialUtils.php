<?php
namespace CakeDC\Users\Auth\Social\Util;

use League\OAuth2\Client\Provider\AbstractProvider;
use ReflectionClass;

/**
 * Created by PhpStorm.
 * User: ajibarra
 * Date: 10/17/15
 * Time: 3:45 PM
 */
class SocialUtils
{
    /**
     * Get provider from classname
     * @param AbstractProvider $provider
     * @return string
     */
    public static function getProvider(AbstractProvider $provider)
    {
        $reflect = new ReflectionClass($provider);
        return $reflect->getShortName();
    }
}