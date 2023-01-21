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

use Authorization\AuthorizationServiceInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AuthorizationServiceProvider
 *
 * @package CakeDC\Users\Provider
 */
class AuthorizationServiceProvider implements AuthorizationServiceProviderInterface
{
    use ServiceProviderLoaderTrait;

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request Http server request
     * @return \Authorization\AuthorizationService
     */
    public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
    {
        $key = 'Auth.Authorization.serviceLoader';

        return $this->loadService($request, $key);
    }
}
