<?php

namespace CakeDC\Users\Middleware;

use CakeDC\Users\Authenticator\SocialAuthenticator;
use CakeDC\Users\Exception\MissingEmailException;
use CakeDC\Users\Exception\SocialAuthenticationException;
use CakeDC\Users\Social\Service\ServiceFactory;
use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use Cake\Event\EventDispatcherTrait;
use Cake\Http\ServerRequest;
use Cake\Log\LogTrait;
use Cake\Routing\Router;
use Psr\Http\Message\ResponseInterface;

class SocialAuthMiddleware
{
    use EventDispatcherTrait;
    use InstanceConfigTrait;
    use LogTrait;

    const AUTH_ERROR_MISSING_EMAIL = 10;
    const AUTH_ERROR_ACCOUNT_NOT_ACTIVE = 20;
    const AUTH_ERROR_USER_NOT_ACTIVE = 30;
    const AUTH_ERROR_INVALID_RECAPTCHA = 40;
    const AUTH_ERROR_FIND_USER = 50;
    const AUTH_SUCCESS = 100;

    const ATTRIBUTE_NAME_SOCIAL_RAW_DATA = 'socialRawData';
    const ATTRIBUTE_NAME_SOCIAL_AUTH_STATUS = 'socialAuthStatus';

    protected $_defaultConfig = [];
    protected $authStatus = 0;
    protected $rawData = [];

    /**
     * @var \CakeDC\Users\Social\Service\ServiceInterface
     */
    protected $service;

    protected $params = [
        'plugin' => 'CakeDC/Users',
        'controller' => 'Users',
        'action' => 'socialLogin'
    ];

    /**
     * Perform social auth
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @param callable $next Callback to invoke the next middleware.
     * @return \Psr\Http\Message\ResponseInterface A response
     */
    public function __invoke(ServerRequest $request, ResponseInterface $response, $next)
    {
        $action = $request->getParam('action');
        if ($action !== 'socialLogin' || $request->getParam('plugin') !== 'CakeDC/Users') {
            return $next($request, $response);
        }

        $service = (new ServiceFactory())->createFromRequest($request);
        if (!$service->isGetUserStep($request)) {
            return $response->withLocation($service->getAuthorizationUrl($request));
        }
        $request = $request->withAttribute(SocialAuthenticator::SOCIAL_SERVICE_ATTRIBUTE, $service);

        return $this->goNext($request, $response, $next);
    }

    /**
     * Handle SocialAuthenticationException
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @param \CakeDC\Users\Exception\SocialAuthenticationException $exception Exception thrown
     *
     * @return \Psr\Http\Message\ResponseInterface A response
     */
    protected function onAuthenticationException(ServerRequest $request, ResponseInterface $response, $exception)
    {
        $baseClassName = get_class($exception->getPrevious());
        if ($baseClassName === MissingEmailException::class) {
            $this->setErrorMessage($request, __d('CakeDC/Users', 'Please enter your email'));

            $request->getSession()->write(
                Configure::read('Users.Key.Session.social'),
                $exception->getAttributes()['rawData']
            );

            return $this->responseWithActionLocation($response, 'socialEmail');
        }

        $this->setErrorMessage($request, __d('CakeDC/Users', 'Could not identify your account, please try again'));

        return $this->responseWithActionLocation($response, 'login');
    }

    /**
     * Set request error message
     *
     * @param ServerRequest $request the request with session attribute
     * @param string $message the message
     *
     * @return void
     */
    private function setErrorMessage(ServerRequest $request, $message)
    {
        $messages = (array)$request->getSession()->read('Flash.flash');
        $messages[] = [
            'key' => 'flash',
            'element' => 'Flash/error',
            'params' => [],
            'message' => $message
        ];
        $request->getSession()->write('Flash.flash', $messages);
    }

    /**
     * Set location header to response using the string action
     *
     * @param ResponseInterface $response to set location header
     * @param string $action action at users controller
     * @return ResponseInterface
     */
    protected function responseWithActionLocation(ResponseInterface $response, $action)
    {
        $url = Router::url(compact('action') + $this->params);

        return $response->withLocation($url);
    }

    /**
     * Go to next handling SocialAuthenticationException
     *
     * @param ServerRequest $request The request
     * @param ResponseInterface $response The response
     * @param callable $next next middleware
     * @return ResponseInterface
     */
    protected function goNext(ServerRequest $request, ResponseInterface $response, $next)
    {
        try {
            return $next($request, $response);
        } catch (SocialAuthenticationException $exception) {
            return $this->onAuthenticationException($request, $response, $exception);
        }
    }
}
