<?php

namespace CakeDC\Users\Authenticator;

use Authentication\UrlChecker\UrlCheckerTrait;
use CakeDC\Auth\Authenticator\SocialAuthenticator as BaseAuthenticator;

/**
 * Social authenticator
 *
 * Authenticates an identity based on request attribute socialService (CakeDC\Auth\Social\Service\ServiceInterface)
 */
class SocialAuthenticator extends BaseAuthenticator
{
    use SocialAuthTrait;
    use UrlCheckerTrait;

    const FAILURE_ACCOUNT_NOT_ACTIVE = 'FAILURE_ACCOUNT_NOT_ACTIVE';

    const FAILURE_USER_NOT_ACTIVE = 'FAILURE_USER_NOT_ACTIVE';
}
