<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

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

    public const FAILURE_ACCOUNT_NOT_ACTIVE = 'FAILURE_ACCOUNT_NOT_ACTIVE';

    public const FAILURE_USER_NOT_ACTIVE = 'FAILURE_USER_NOT_ACTIVE';
}
