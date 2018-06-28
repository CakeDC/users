<?php
namespace CakeDC\Users;

use Authentication\AuthenticationServiceInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use CakeDC\Auth\Middleware\RbacMiddleware;
use CakeDC\Users\Middleware\SocialAuthMiddleware;
use CakeDC\Users\Middleware\SocialEmailMiddleware;

class Plugin extends BasePlugin
{
    const EVENT_AFTER_CHANGE_PASSWORD = 'Users.Managment.afterResetPassword';
    /**
     * load authenticators and identifiers
     *
     * @param AuthenticationServiceInterface $service Base authentication service
     * @return AuthenticationServiceInterface
     */
    public function authentication(AuthenticationServiceInterface $service)
    {
        $authenticators = Configure::read('Auth.Authenticators');
        $identifiers = Configure::read('Auth.Identifiers');

        foreach($identifiers as $identifier => $options) {
            if (is_numeric($identifier)) {
                $identifier = $options;
                $options = [];
            }

            $service->loadIdentifier($identifier, $options);
        }

        foreach($authenticators as $authenticator => $options) {
            if (is_numeric($authenticator)) {
                $authenticator = $options;
                $options = [];
            }

            $service->loadAuthenticator($authenticator, $options);
        }

        if (Configure::read('Users.GoogleAuthenticator.login')) {
            $service->loadAuthenticator('CakeDC/Users.GoogleTwoFactor', [
                'skipGoogleVerify' => true,
            ]);
        }

        return $service;
    }

    /**
     * {@inheritdoc}
     */
    public function middleware($middlewareQueue)
    {
        if (Configure::read('Users.Social.login')) {
            $middlewareQueue
                ->add(SocialAuthMiddleware::class)
                ->add(SocialEmailMiddleware::class);
        }

        $authentication = new AuthenticationMiddleware($this);
        $middlewareQueue->add($authentication);
        if (Configure::read('Users.GoogleAuthenticator.login')) {
            $middlewareQueue->add('CakeDC\Users\Middleware\GoogleAuthenticatorMiddleware');
        }

        $middlewareQueue->add(new RbacMiddleware(null, [
            'unauthorizedRedirect' => [
                'prefix' => false,
                'plugin' => 'CakeDC/Users',
                'controller' => 'Users',
                'action' => 'login',
            ]
        ]));

        return $middlewareQueue;
    }
}