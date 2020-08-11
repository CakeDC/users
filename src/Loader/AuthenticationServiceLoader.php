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

namespace CakeDC\Users\Loader;

use Cake\Core\Configure;
use CakeDC\Auth\Authentication\AuthenticationService;
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
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
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
        foreach ($identifiers as $key => $item) {
            [$identifier, $options] = $this->_getItemLoadData($item, $key);

            $service->loadIdentifier($identifier, $options);
        }
    }

    /**
     * Load the authenticators defined at config Auth.Authenticators
     *
     * @param \CakeDC\Auth\Authentication\AuthenticationService $service Authentication service to load identifiers
     * @return void
     */
    protected function loadAuthenticators($service)
    {
        $authenticators = Configure::read('Auth.Authenticators');

        foreach ($authenticators as $key => $item) {
            [$authenticator, $options] = $this->_getItemLoadData($item, $key);

            $service->loadAuthenticator($authenticator, $options);
        }
    }

    /**
     * Load the CakeDC/Auth.TwoFactor based on config OneTimePasswordAuthenticator.login
     *
     * @param \CakeDC\Auth\Authentication\AuthenticationService $service Authentication service to load identifiers
     * @return void
     */
    protected function loadTwoFactorAuthenticator($service)
    {
        if (
            Configure::read('OneTimePasswordAuthenticator.login') !== false
            || Configure::read('U2f.enabled') !== false
        ) {
            $service->loadAuthenticator('CakeDC/Auth.TwoFactor', [
                'skipTwoFactorVerify' => true,
            ]);
        }
    }

    /**
     * @param mixed $item Item configuration or className
     * @param string $key Item array key.
     * @return array
     */
    protected function _getItemLoadData($item, $key)
    {
        $options = [];
        if (!is_array($item)) {
            return [$item, $options];
        }
        $options = $item;
        if (!isset($options['className'])) {
            throw new \InvalidArgumentException(
                __('Property  {0}.className should be defined', $key)
            );
        }
        $className = $options['className'];
        unset($options['className']);

        return [$className, $options];
    }
}
