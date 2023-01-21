<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2020, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Provider;

use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AuthenticationServiceProvider
 *
 * @package CakeDC\Users\Provider
 */
class AuthenticationServiceProvider implements AuthenticationServiceProviderInterface
{
    use ServiceProviderLoaderTrait;

    /**
     * @param \Psr\Http\Message\ServerRequestInterface  $request Http server request
     * @return \Authentication\AuthenticationServiceInterface
     */
    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        $key = 'Auth.Authentication.serviceLoader';

        return $this->loadService($request, $key);
    }
}
