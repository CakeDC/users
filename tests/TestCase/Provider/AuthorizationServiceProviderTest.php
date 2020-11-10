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

namespace CakeDC\Users\Test\TestCase\Provider;

use Authorization\AuthorizationService;
use Authorization\Policy\ResolverCollection;
use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\Http\ServerRequestFactory;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Provider\AuthorizationServiceProvider;

/**
 * Class AuthorizationServiceProviderTest
 *
 * @package CakeDC\Users\Test\TestCase\Provider
 */
class AuthorizationServiceProviderTest extends TestCase
{
    /**
     * testGetAuthorizationService
     *
     * @return void
     */
    public function testGetAuthorizationService()
    {
        $authorizationServiceProvider = new AuthorizationServiceProvider();
        $service = $authorizationServiceProvider->getAuthorizationService(new ServerRequest());
        $this->assertInstanceOf(AuthorizationService::class, $service);
    }

    /**
     * testGetAuthorizationService
     *
     * @return void
     */
    public function testGetAuthorizationServiceCallableDefined()
    {
        $request = ServerRequestFactory::fromGlobals();
        $request->withQueryParams(['method' => __METHOD__]);
        $service = new AuthorizationService(new ResolverCollection());
        Configure::write('Auth.Authorization.serviceLoader', function ($aRequest) use ($request, $service) {
            $this->assertSame($request, $aRequest);

            return $service;
        });

        $authorizationServiceProvider = new AuthorizationServiceProvider();
        $actualService = $authorizationServiceProvider->getAuthorizationService($request);
        $this->assertSame($service, $actualService);
    }
}
