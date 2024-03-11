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

use Authentication\Authenticator\SessionAuthenticator;
use Authentication\Authenticator\TokenAuthenticator;
use Authentication\Identifier\JwtSubjectIdentifier;
use Authentication\Identifier\PasswordIdentifier;
use Authentication\Identifier\TokenIdentifier;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Http\ServerRequestFactory;
use Cake\TestSuite\TestCase;
use CakeDC\Auth\Authentication\AuthenticationService as CakeDCAuthenticationService;
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
            ],
            'Token' => [
                'className' => 'Authentication.Token',
                'skipTwoFactorVerify' => true,
                'header' => null,
                'queryParam' => 'api_key',
                'tokenPrefix' => null,
            ],
        ]);
        Configure::write('Auth.Identifiers', [
            'Password' => [
                'className' => 'Authentication.Password',
                'fields' => [
                    'username' => 'email_2',
                    'password' => 'password_2',
                ],
            ],
            'Token' => [
                'className' => 'Authentication.Token',
                'tokenField' => 'api_token',
            ],
            'Authentication.JwtSubject',
        ]);
        Configure::write('OneTimePasswordAuthenticator.login', true);
        Configure::write('TwoFactorProcessors', [
            \CakeDC\Auth\Authentication\TwoFactorProcessor\OneTimePasswordProcessor::class,
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
            ],
            FormAuthenticator::class => [
                'loginUrl' => '/login',
                'keyCheckEnabledRecaptcha' => 'Users.reCaptcha.login',
                'fields' => ['username' => 'email', 'password' => 'alt_password'],
            ],
            TokenAuthenticator::class => [
                'header' => null,
                'queryParam' => 'api_key',
                'tokenPrefix' => null,
                'skipTwoFactorVerify' => true,
            ],
            TwoFactorAuthenticator::class => [
                'loginUrl' => null,
                'urlChecker' => 'Authentication.Default',
                'skipTwoFactorVerify' => true,
            ],
        ];
        $actual = [];
        foreach ($authenticators as $key => $value) {
            $config = $value->getConfig();
            unset($config['impersonateSessionKey']);
            $actual[get_class($value)] = $config;
        }
        $this->assertEquals($expected, $actual);

        /**
         * @var \Authentication\Identifier\IdentifierCollection $identifiers
         */
        $identifiers = $service->identifiers();
        $expected = [
            PasswordIdentifier::class => [
                'fields' => [
                    'username' => 'email_2',
                    'password' => 'password_2',
                ],
                'resolver' => 'Authentication.Orm',
                'passwordHasher' => null,
            ],
            TokenIdentifier::class => [
                'tokenField' => 'api_token',
                'dataField' => 'token',
                'resolver' => 'Authentication.Orm',
            ],
            JwtSubjectIdentifier::class => [
                'tokenField' => 'id',
                'dataField' => 'sub',
                'resolver' => 'Authentication.Orm',
            ],
        ];
        $actual = [];
        foreach ($identifiers as $key => $value) {
            $config = $value->getConfig();
            unset($config['impersonateSessionKey'], $config['hashAlgorithm']);
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
        $request->withQueryParams(['method' => __METHOD__]);
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
            ],
            'Form' => [
                'className' => 'CakeDC/Auth.Form',
                'loginUrl' => '/login',
                'fields' => ['username' => 'email', 'password' => 'alt_password'],
            ],
            'Token' => [
                'className' => 'Authentication.Token',
                'skipTwoFactorVerify' => true,
                'header' => null,
                'queryParam' => 'api_key',
                'tokenPrefix' => null,
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
            ],
            FormAuthenticator::class => [
                'loginUrl' => '/login',
                'fields' => ['username' => 'email', 'password' => 'alt_password'],
                'keyCheckEnabledRecaptcha' => 'Users.reCaptcha.login',
            ],
            TokenAuthenticator::class => [
                'header' => null,
                'queryParam' => 'api_key',
                'tokenPrefix' => null,
                'skipTwoFactorVerify' => true,
            ],
        ];
        $actual = [];
        foreach ($authenticators as $key => $value) {
            $config = $value->getConfig();
            unset($config['impersonateSessionKey']);
            $actual[get_class($value)] = $config;
        }
        $this->assertEquals($expected, $actual);
    }
}
