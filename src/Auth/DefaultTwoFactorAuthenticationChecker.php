<?php
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
        return empty($user) && $this->isEnabled();
    }

}
