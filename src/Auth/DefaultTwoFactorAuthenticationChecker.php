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
        return (bool)Configure::read('Users.GoogleAuthenticator.login');
    }

    /**
     * Check if two factor authentication is required for a user
     *
     * @param array $user user data
     *
     * @return bool
     */
    public function isRequired(array $user)
    {
        if (empty($user)) {
            throw new BadRequestException("User data can't be empty");
        }

        return $this->isEnabled();
    }

}
