<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2020, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Provider;

use Authentication\Authenticator\SessionAuthenticator;
use Authentication\Authenticator\TokenAuthenticator;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Http\ServerRequestFactory;
use Cake\TestSuite\TestCase;
use CakeDC\Auth\Authentication\AuthenticationService as CakeDCAuthenticationService;
use CakeDC\Auth\Authentication\TwoFactorProcessor\OneTimePasswordProcessor;
use CakeDC\Auth\Authenticator\FormAuthenticator;
use CakeDC\Auth\Authenticator\TwoFactorAuthenticator;
use CakeDC\Users\Provider\AuthenticationServiceProvider;

/**
 * Class AuthenticationServiceProviderTest
 *
 * @package CakeDC\Users\Test\TestCase\Provider
 */
class AuthenticationServiceProviderTest extends TestCase
{
    /**
     * testGetAuthenticationService
     *
     * @return void
     */
    public function testGetAuthenticationService()
    {
        Configure::write('Auth.Authenticators', [
            'Session' => [
                'className' => 'Authentication.Session',
                'skipTwoFactorVerify' => true,
                'sessionKey' => 'CustomAuth',
                'fields' => ['username' => 'email'],
                'identify' => true,
            ],
            'Form' => [
                'className' => 'CakeDC/Auth.Form',
                'loginUrl' => '/login',
                'fields' => ['username' => 'email', 'password' => 'alt_password'],
                'identifier' => [
                    'className' => 'Authentication.Password',
                    'fields' => [
                        'username' => ['username', 'email'],
                        'password' => 'password',
                    ],
                    'resolver' => [
                        'className' => 'Authentication.Orm',
                        'finder' => 'active',
                    ],
                ],
            ],
            'Token' => [
                'className' => 'Authentication.Token',
                'skipTwoFactorVerify' => true,
                'header' => null,
                'queryParam' => 'api_key',
                'tokenPrefix' => null,
                'identifier' => [
                    'className' => 'Authentication.Token',
                    'tokenField' => 'api_token',
                    'resolver' => [
                        'className' => 'Authentication.Orm',
                        'finder' => 'active',
                    ],
                ],
            ],
        ]);
        Configure::write('OneTimePasswordAuthenticator.login', true);
        Configure::write('TwoFactorProcessors', [
            OneTimePasswordProcessor::class,
        ]);

        $authenticationServiceProvider = new AuthenticationServiceProvider();
        $service = $authenticationServiceProvider->getAuthenticationService(new ServerRequest(), new Response());
        $this->assertInstanceOf(CakeDCAuthenticationService::class, $service);

        /**
         * @var \Authentication\Authenticator\AuthenticatorCollection $authenticators
         */
        $authenticators = $service->authenticators();
        $expected = [
            SessionAuthenticator::class => [
                'fields' => ['username' => 'email'],
                'sessionKey' => 'CustomAuth',
                'identify' => true,
                'identityAttribute' => 'identity',
                'skipTwoFactorVerify' => true,
                'impersonateSessionKey' => 'AuthImpersonate',
                'className' => 'Authentication.Session',
            ],
            FormAuthenticator::class => [
                'loginUrl' => '/login',
                'keyCheckEnabledRecaptcha' => 'Users.reCaptcha.login',
                'fields' => ['username' => 'email', 'password' => 'alt_password'],
                'className' => 'CakeDC/Auth.Form',
                'identifier' => [
                    'className' => 'Authentication.Password',
                    'fields' => [
                        'username' => ['username', 'email'],
                        'password' => 'password',
                    ],
                    'resolver' => [
                        'className' => 'Authentication.Orm',
                        'finder' => 'active',
                    ],
                ],
            ],
            TokenAuthenticator::class => [
                'header' => null,
                'queryParam' => 'api_key',
                'tokenPrefix' => null,
                'skipTwoFactorVerify' => true,
                'className' => 'Authentication.Token',
                'identifier' => [
                    'className' => 'Authentication.Token',
                    'tokenField' => 'api_token',
                    'resolver' => [
                        'className' => 'Authentication.Orm',
                        'finder' => 'active',
                    ],
                ],
            ],
            TwoFactorAuthenticator::class => [
                'loginUrl' => null,
                'urlChecker' => 'Authentication.Default',
                'skipTwoFactorVerify' => true,
                'className' => 'CakeDC/Auth.TwoFactor',
            ],
        ];
        $actual = [];
        foreach ($authenticators as $value) {
            $config = $value->getConfig();
            $actual[get_class($value)] = $config;
        }
        $this->assertEquals($expected, $actual);
    }

    /**
     * testGetAuthenticationService
     *
     * @return void
     */
    public function testGetAuthenticationServiceCallableDefined()
    {
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withQueryParams(['method' => __METHOD__]);
        $service = new CakeDCAuthenticationService([
            'identifiers' => [
                'Authentication.Password',
            ],
        ]);
        Configure::write('Auth.Authentication.serviceLoader', function ($aRequest) use ($request, $service) {
            $this->assertSame($request, $aRequest);

            return $service;
        });

        $authenticationServiceProvider = new AuthenticationServiceProvider();
        $actualService = $authenticationServiceProvider->getAuthenticationService($request);
        $this->assertSame($service, $actualService);
    }

    /**
     * testGetAuthenticationService
     *
     * @return void
     */
    public function testGetAuthenticationServiceWithoutOneTimePasswordAuthenticator()
    {
        Configure::write('Auth.Authenticators', [
            'Session' => [
                'className' => 'Authentication.Session',
                'skipTwoFactorVerify' => true,
                'sessionKey' => 'CustomAuth',
                'fields' => ['username' => 'email'],
                'identify' => true,
                'impersonateSessionKey' => 'AuthImpersonate',
            ],
            'Form' => [
                'className' => 'CakeDC/Auth.Form',
                'loginUrl' => '/login',
                'fields' => ['username' => 'email', 'password' => 'alt_password'],
                'identifier' => [
                    'className' => 'Authentication.Password',
                    'fields' => ['username' => 'email', 'password' => 'alt_password'],
                    'resolver' => [
                        'className' => 'Authentication.Orm',
                        'finder' => 'active',
                    ],
                ],
            ],
            'Token' => [
                'className' => 'Authentication.Token',
                'skipTwoFactorVerify' => true,
                'header' => null,
                'queryParam' => 'api_key',
                'tokenPrefix' => null,
                'identifier' => [
                    'className' => 'Authentication.Token',
                    'tokenField' => 'api_token',
                    'resolver' => [
                        'className' => 'Authentication.Orm',
                        'finder' => 'active',
                    ],
                ],
            ],
        ]);
        Configure::write('Auth.Identifiers', [
            'Authentication.Password',
            'Token' => [
                'className' => 'Authentication.Token',
                'tokenField' => 'api_token',
            ],
            'Authentication.JwtSubject',
        ]);
        Configure::write('OneTimePasswordAuthenticator.login', false);
        Configure::write('TwoFactorProcessors', []);

        $authenticationServiceProvider = new AuthenticationServiceProvider();
        $service = $authenticationServiceProvider->getAuthenticationService(new ServerRequest(), new Response());
        $this->assertInstanceOf(CakeDCAuthenticationService::class, $service);

        /**
         * @var \Authentication\Authenticator\AuthenticatorCollection $authenticators
         */
        $authenticators = $service->authenticators();
        $expected = [
            SessionAuthenticator::class => [
                'fields' => ['username' => 'email'],
                'sessionKey' => 'CustomAuth',
                'identify' => true,
                'identityAttribute' => 'identity',
                'skipTwoFactorVerify' => true,
                'impersonateSessionKey' => 'AuthImpersonate',
                'className' => 'Authentication.Session',
            ],
            FormAuthenticator::class => [
                'loginUrl' => '/login',
                'fields' => ['username' => 'email', 'password' => 'alt_password'],
                'keyCheckEnabledRecaptcha' => 'Users.reCaptcha.login',
                'className' => 'CakeDC/Auth.Form',
                'identifier' => [
                    'className' => 'Authentication.Password',
                    'fields' => ['username' => 'email', 'password' => 'alt_password'],
                    'resolver' => [
                        'className' => 'Authentication.Orm',
                        'finder' => 'active',
                    ],
                ],
            ],
            TokenAuthenticator::class => [
                'header' => null,
                'queryParam' => 'api_key',
                'tokenPrefix' => null,
                'skipTwoFactorVerify' => true,
                'className' => 'Authentication.Token',
                'identifier' => [
                    'className' => 'Authentication.Token',
                    'tokenField' => 'api_token',
                    'resolver' => [
                        'className' => 'Authentication.Orm',
                        'finder' => 'active',
                    ],
                ],
            ],
        ];
        $actual = [];
        foreach ($authenticators as $value) {
            $config = $value->getConfig();
            $actual[get_class($value)] = $config;
        }
        $this->assertEquals($expected, $actual);
    }
}
