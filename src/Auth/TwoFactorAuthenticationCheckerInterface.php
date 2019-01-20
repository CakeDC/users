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

interface TwoFactorAuthenticationCheckerInterface
{
    /**
     * Check if two factor authentication is enabled
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Check if two factor authentication is required for a user
     *
     * @param array $user user data
     *
     * @return bool
     */
    public function isRequired(array $user = null);
}
