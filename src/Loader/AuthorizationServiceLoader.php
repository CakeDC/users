<?php
/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Loader;

use Authorization\AuthorizationService;
use Authorization\Policy\MapResolver;
use Authorization\Policy\OrmResolver;
use Authorization\Policy\ResolverCollection;
use CakeDC\Auth\Policy\CollectionPolicy;
use CakeDC\Auth\Policy\RbacPolicy;
use Cake\Http\ServerRequest;
use CakeDC\Auth\Policy\SuperuserPolicy;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizationServiceLoader
{
    /**
     * Load the authorization service with OrmResolver and Map Resolver for RbacPolicy
     *
     * @param ServerRequestInterface $request The request.
     * @return AuthorizationService
     */
    public function __invoke(ServerRequestInterface $request)
    {
        $map = new MapResolver();
        $map->map(
            ServerRequest::class,
            new CollectionPolicy([
                SuperuserPolicy::class,
                RbacPolicy::class
            ])
        );

        $orm = new OrmResolver();

        $resolver = new ResolverCollection([
            $map,
            $orm
        ]);

        return new AuthorizationService($resolver);
    }
}