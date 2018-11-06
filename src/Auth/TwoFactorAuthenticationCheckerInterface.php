<?php

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
    public function isRequired(array $user);
}