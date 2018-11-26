<?php
/**
 * Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Loader;

use CakeDC\Auth\Authentication\AuthenticationService;
use Cake\Core\Configure;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AuthenticationServiceLoader
 *
 * @package CakeDC\Users\Loader
 */
class AuthenticationServiceLoader
{
    /**
     * Load the authentication service with authenticators from config Auth.Authenticators,
     * and identifiers from config Auth.Identifiers.
     *
     * @param ServerRequestInterface $request The request.
     * @return \CakeDC\Auth\Authentication\AuthenticationService
     */
    public function __invoke(ServerRequestInterface $request)
    {
        $service = new AuthenticationService();
        $this->loadIdentifiers($service);
        $this->loadAuthenticators($service);
        $this->loadTwoFactorAuthenticator($service);

        return $service;
    }

    /**
     * Load the indentifiers defined at config Auth.Identifiers
     *
     * @param \CakeDC\Auth\Authentication\AuthenticationService $service Authentication service to load identifiers
     * @return void
     */
    protected function loadIdentifiers($service)
    {
        $identifiers = Configure::read('Auth.Identifiers');
        foreach ($identifiers as $identifier => $options) {
            if (is_numeric($identifier)) {
                $identifier = $options;
                $options = [];
            }

            $service->loadIdentifier($identifier, $options);
        }
    }

    /**
     * Load the authenticators defined at config Auth.Authenticators
     *
     * @param \CakeDC\Auth\Authentication\AuthenticationService $service Authentication service to load identifiers
     *
     * @return void
     */
    protected function loadAuthenticators($service)
    {
        $authenticators = Configure::read('Auth.Authenticators');

        foreach ($authenticators as $authenticator => $options) {
            if (is_numeric($authenticator)) {
                $authenticator = $options;
                $options = [];
            }

            $service->loadAuthenticator($authenticator, $options);
        }
    }

    /**
     * Load the CakeDC/Auth.TwoFactor based on config OneTimePasswordAuthenticator.login
     *
     * @param \CakeDC\Auth\Authentication\AuthenticationService $service Authentication service to load identifiers
     *
     * @return void
     */
    protected function loadTwoFactorAuthenticator($service)
    {
        if (Configure::read('OneTimePasswordAuthenticator.login') !== false) {
            $service->loadAuthenticator('CakeDC/Auth.TwoFactor', [
                'skipGoogleVerify' => true,
            ]);
        }
    }
}