<?php
/**
 * Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace CakeDC\Users\Auth;

use Cake\Core\Configure;
use Cake\Network\Exception\BadRequestException;

/**
 * Factory for two authentication checker
 *
 * @package CakeDC\Users\Auth
 */
class TwoFactorAuthenticationCheckerFactory
{
    /**
     * Get the two factor authentication checker
     *
     * @return TwoFactorAuthenticationCheckerInterface
     */
    public function build()
    {
        $className = Configure::read('GoogleAuthenticator.checker');
        $interfaces = class_implements($className);
        $required = TwoFactorAuthenticationCheckerInterface::class;

        if (in_array($required, $interfaces)) {
            return new $className();
        }
        throw new \InvalidArgumentException("Invalid config for 'GoogleAuthenticator.checker', '$className' does not implement '$required'");
    }
}
