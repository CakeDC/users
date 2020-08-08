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

use Authentication\Authenticator\Result;
use CakeDC\Users\Exception\AccountNotActiveException;
use CakeDC\Users\Exception\MissingEmailException;
use CakeDC\Users\Exception\SocialAuthenticationException;
use CakeDC\Users\Exception\UserNotActiveException;
use CakeDC\Users\Identifier\SocialIdentifier;

trait SocialAuthTrait
{
    /**
     * @param array $rawData social user raw data
     * @return \Authentication\Authenticator\Result
     */
    protected function identify($rawData)
    {
        try {
            $user = $this->getIdentifier()->identify([SocialIdentifier::CREDENTIAL_KEY => $rawData]);
            if (!empty($user)) {
                return new Result($user, Result::SUCCESS);
            }

            return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND);
        } catch (AccountNotActiveException $e) {
            return new Result(null, self::FAILURE_ACCOUNT_NOT_ACTIVE);
        } catch (UserNotActiveException $e) {
            return new Result(null, self::FAILURE_USER_NOT_ACTIVE);
        } catch (MissingEmailException $exception) {
            throw new SocialAuthenticationException(compact('rawData'), null, $exception);
        }
    }
}
