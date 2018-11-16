<?php
namespace CakeDC\Users;

use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceProviderInterface;
use Authorization\Middleware\AuthorizationMiddleware;
use Authorization\Middleware\RequestAuthorizationMiddleware;
use Authorization\Policy\MapResolver;
use Authorization\Policy\OrmResolver;
use Authorization\Policy\ResolverCollection;
use CakeDC\Auth\Middleware\RbacMiddleware;
use CakeDC\Users\Authentication\AuthenticationService;
use CakeDC\Users\Middleware\OneTimePasswordAuthenticatorMiddleware;
use CakeDC\Users\Middleware\SocialAuthMiddleware;
use CakeDC\Users\Middleware\SocialEmailMiddleware;
use CakeDC\Users\Policy\RbacPolicy;
use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Http\MiddlewareQueue;
use Cake\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Plugin extends BasePlugin implements AuthenticationServiceProviderInterface, AuthorizationServiceProviderInterface
{
    const EVENT_AFTER_LOGIN = 'Users.Authentication.afterLogin';
    const EVENT_BEFORE_LOGOUT = 'Users.Authentication.beforeLogout';
    const EVENT_AFTER_LOGOUT = 'Users.Authentication.afterLogout';
    const EVENT_FAILED_SOCIAL_LOGIN = 'Users.Authentication.failedSocialLogin';
    const EVENT_AFTER_SOCIAL_REGISTER = 'Users.Authentication.afterSocialRegister';

    const EVENT_BEFORE_REGISTER = 'Users.Managment.beforeRegister';
    const EVENT_AFTER_REGISTER = 'Users.Managment.afterRegister';
    const EVENT_AFTER_CHANGE_PASSWORD = 'Users.Managment.afterResetPassword';
    const EVENT_BEFORE_SOCIAL_LOGIN_USER_CREATE = 'Users.Managment.beforeSocialLoginUserCreate';
    const EVENT_ON_EXPIRED_TOKEN = 'Users.Managment.onExpiredToken';
    const EVENT_AFTER_RESEND_TOKEN_VALIDATION = 'Users.Managment.afterResendTokenValidation';

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
     * {@inheritdoc}
     */
    public function getAuthorizationService(ServerRequestInterface $request, ResponseInterface $response)
    {
        $map = new MapResolver();
        $map->map(ServerRequest::class, RbacPolicy::class);

        $orm = new OrmResolver();

        $resolver = new ResolverCollection([
            $map,
            $orm
        ]);

        return new AuthorizationService($resolver);
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

        foreach ($identifiers as $identifier => $options) {
            if (is_numeric($identifier)) {
                $identifier = $options;
                $options = [];
            }

            $service->loadIdentifier($identifier, $options);
        }

        foreach ($authenticators as $authenticator => $options) {
            if (is_numeric($authenticator)) {
                $authenticator = $options;
                $options = [];
            }

            $service->loadAuthenticator($authenticator, $options);
        }

        if (Configure::read('Users.OneTimePasswordAuthenticator.login')) {
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

        if (Configure::read('Users.OneTimePasswordAuthenticator.login')) {
            $middlewareQueue->add(OneTimePasswordAuthenticatorMiddleware::class);
        }

        $middlewareQueue = $this->addAuthorizationMiddleware($middlewareQueue);

        return $middlewareQueue;
    }

    /**
     * Add authorization middleware based on Auth.Authorization
     *
     * @param MiddlewareQueue $middlewareQueue queue of middleware
     * @return MiddlewareQueue
     */
    protected function addAuthorizationMiddleware(MiddlewareQueue $middlewareQueue)
    {
        if (Configure::read('Auth.Authorization.enable') === false) {
            return $middlewareQueue;
        }

        if (Configure::read('Auth.Authorization.loadAuthorizationMiddleware') !== false) {
            $middlewareQueue->add(new AuthorizationMiddleware($this, Configure::read('Auth.AuthorizationMiddleware')));
            $middlewareQueue->add(new RequestAuthorizationMiddleware());
        }

        if (Configure::read('Auth.Authorization.loadRbacMiddleware') !== false) {
            $middlewareQueue->add(new RbacMiddleware(null, Configure::read('Auth.RbacMiddleware')));
        }

        return $middlewareQueue;
    }
}
