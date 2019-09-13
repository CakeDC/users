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

use Authentication\Middleware\AuthenticationMiddleware;
use Authorization\Middleware\AuthorizationMiddleware;
use Authorization\Middleware\RequestAuthorizationMiddleware;
use CakeDC\Auth\Middleware\OneTimePasswordAuthenticatorMiddleware;
use CakeDC\Auth\Middleware\RbacMiddleware;
use CakeDC\Users\Middleware\SocialAuthMiddleware;
use CakeDC\Users\Middleware\SocialEmailMiddleware;
use CakeDC\Users\Plugin;
use Cake\Core\Configure;
use Cake\Http\MiddlewareQueue;

/**
 * Class MiddlewareQueueLoader
 *
 * @package CakeDC\Users\Loader
 */
class MiddlewareQueueLoader
{
    /**
     * Load the middlewares need in this plugin based on users configurations.
     *
     * Always load AuthenticationMiddleware;
     * For 'Users.Social.login' load SocialAuthMiddleware, SocialEmailMiddleware;
     * For 'OneTimePasswordAuthenticator.login' load OneTimePasswordAuthenticatorMiddleware;
     * For 'Auth.Authorization.loadAuthorizationMiddleware' load AuthorizationMiddleware and RequestAuthorizationMiddleware;
     * For 'Auth.Authorization.loadRbacMiddleware' load RbacMiddleware
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to update.
     * @param \CakeDC\Users\Plugin $plugin Users plugin object
     *
     * @return \Cake\Http\MiddlewareQueue
     */
    public function __invoke(MiddlewareQueue $middlewareQueue, Plugin $plugin)
    {
        $this->loadSocialMiddleware($middlewareQueue);
        $this->loadAuthenticationMiddleware($middlewareQueue, $plugin);
        $this->load2faMiddleware($middlewareQueue);

        return $this->loadAuthorizationMiddleware($middlewareQueue, $plugin);
    }

    /**
     * Load social middlewares if enabled. Based on config 'Users.Social.login'
     *
     * @param MiddlewareQueue $middlewareQueue
     *
     * @return void
     */
    protected function loadSocialMiddleware(MiddlewareQueue $middlewareQueue)
    {
        if (Configure::read('Users.Social.login')) {
            $middlewareQueue
                ->add(SocialAuthMiddleware::class)
                ->add(SocialEmailMiddleware::class);
        }
    }

    /**
     * Load authentication middleware
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue queue of middleware
     * @param \CakeDC\Users\Plugin $plugin Users plugin object
     *
     * @return void
     */
    protected function loadAuthenticationMiddleware(MiddlewareQueue $middlewareQueue, Plugin $plugin)
    {
        $authentication = new AuthenticationMiddleware($plugin);
        $middlewareQueue->add($authentication);
    }

    /**
     * Load OneTimePasswordAuthenticatorMiddleware if enabled. Based on config 'OneTimePasswordAuthenticator.login'
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue queue of middleware
     *
     * @return void
     */
    protected function load2faMiddleware(MiddlewareQueue $middlewareQueue)
    {
        if (Configure::read('OneTimePasswordAuthenticator.login')) {
            $middlewareQueue->add(OneTimePasswordAuthenticatorMiddleware::class);
        }
    }

    /**
     * Load authorization middleware based on Auth.Authorization.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue queue of middleware
     * @param \CakeDC\Users\Plugin $plugin Users plugin object
     *
     * @return \Cake\Http\MiddlewareQueue
     */
    protected function loadAuthorizationMiddleware(MiddlewareQueue $middlewareQueue, Plugin $plugin)
    {
        if (Configure::read('Auth.Authorization.enable') === false) {
            return $middlewareQueue;
        }

        if (Configure::read('Auth.Authorization.loadAuthorizationMiddleware') !== false) {
            $middlewareQueue->add(new AuthorizationMiddleware($plugin, Configure::read('Auth.AuthorizationMiddleware')));
            $middlewareQueue->add(new RequestAuthorizationMiddleware());
        }

        if (Configure::read('Auth.Authorization.loadRbacMiddleware') !== false) {
            $middlewareQueue->add(new RbacMiddleware(null, Configure::read('Auth.RbacMiddleware')));
        }

        return $middlewareQueue;
    }
}