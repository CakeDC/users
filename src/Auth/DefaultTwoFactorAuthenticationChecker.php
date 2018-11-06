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
 * Default class to check if two factor authentication is enabled and required
 *
 * @package CakeDC\Users\Auth
 */
class DefaultTwoFactorAuthenticationChecker implements TwoFactorAuthenticationCheckerInterface
{
    /**
     * Check if two factor authentication is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Configure::read('Users.GoogleAuthenticator.login') !== false;
    }

    /**
     * Check if two factor authentication is required for a user
     *
     * @param array $user user data
     *
     * @return bool
     */
    public function isRequired(array $user = null)
    {
        return !empty($user) && $this->isEnabled();
    }
}
