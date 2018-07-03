<?php
namespace CakeDC\Users;

use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use CakeDC\Auth\Middleware\RbacMiddleware;
use CakeDC\Users\Authentication\AuthenticationService;
use CakeDC\Users\Middleware\GoogleAuthenticatorMiddleware;
use CakeDC\Users\Middleware\SocialAuthMiddleware;
use CakeDC\Users\Middleware\SocialEmailMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Plugin extends BasePlugin implements AuthenticationServiceProviderInterface
{
    const EVENT_AFTER_LOGIN = 'Users.Authentication.afterLogin';
    const EVENT_BEFORE_LOGOUT = 'Users.Authentication.beforeLogout';
    const EVENT_AFTER_LOGOUT = 'Users.Authentication.afterLogout';

    const EVENT_BEFORE_REGISTER = 'Users.Managment.beforeRegister';
    const EVENT_AFTER_REGISTER = 'Users.Managment.afterRegister';
    const EVENT_AFTER_CHANGE_PASSWORD = 'Users.Managment.afterResetPassword';
    const EVENT_BEFORE_SOCIAL_LOGIN_USER_CREATE = 'Users.Managment.beforeSocialLoginUserCreate';

    /**
     * Returns an authentication service instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request
     * @param \Psr\Http\Message\ResponseInterface $response Response
     * @return \Authentication\AuthenticationServiceInterface
     */
    public function getAuthenticationService(ServerRequestInterface $request, ResponseInterface $response)
    {
        return $this->authentication();
    }

    /**
     * load authenticators and identifiers
     *
     * @return AuthenticationServiceInterface
     */
    public function authentication()
    {
        $service = new AuthenticationService();
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
            $middlewareQueue->add(GoogleAuthenticatorMiddleware::class);
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