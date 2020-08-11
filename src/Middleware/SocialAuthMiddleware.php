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

namespace CakeDC\Users\Middleware;

use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Log\LogTrait;
use Cake\Routing\Router;
use CakeDC\Auth\Social\Service\ServiceFactory;
use CakeDC\Users\Authenticator\SocialAuthenticator;
use CakeDC\Users\Exception\MissingEmailException;
use CakeDC\Users\Exception\SocialAuthenticationException;
use CakeDC\Users\Utility\UsersUrl;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SocialAuthMiddleware implements MiddlewareInterface
{
    use LogTrait;

    /**
     * Handle SocialAuthenticationException
     *
     * @param \Cake\Http\ServerRequest $request The request.
     * @param \CakeDC\Users\Exception\SocialAuthenticationException $exception Exception thrown
     * @return \Psr\Http\Message\ResponseInterface A response
     */
    protected function onAuthenticationException(ServerRequest $request, $exception)
    {
        $baseClassName = get_class($exception->getPrevious());
        $response = new Response();
        if ($baseClassName === MissingEmailException::class) {
            $this->setErrorMessage($request, __d('cake_d_c/users', 'Please enter your email'));

            $request->getSession()->write(
                Configure::read('Users.Key.Session.social'),
                $exception->getAttributes()['rawData']
            );

            return $this->responseWithActionLocation($response, 'socialEmail');
        }

        $this->setErrorMessage($request, __d('cake_d_c/users', 'Could not identify your account, please try again'));

        return $this->responseWithActionLocation($response, 'login');
    }

    /**
     * Set request error message
     *
     * @param \Cake\Http\ServerRequest $request the request with session attribute
     * @param string $message the message
     * @return void
     */
    private function setErrorMessage(ServerRequest $request, $message)
    {
        $messages = (array)$request->getSession()->read('Flash.flash');
        $messages[] = [
            'key' => 'flash',
            'element' => 'Flash/error',
            'params' => [],
            'message' => $message,
        ];
        $request->getSession()->write('Flash.flash', $messages);
    }

    /**
     * Set location header to response using the string action
     *
     * @param \Cake\Http\Response $response to set location header
     * @param string $action action at users controller
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function responseWithActionLocation(Response $response, $action)
    {
        $url = Router::url(UsersUrl::actionUrl($action));

        return $response->withLocation($url);
    }

    /**
     * Go to next handling SocialAuthenticationException
     *
     * @param \Cake\Http\ServerRequest $request The request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function goNext(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (SocialAuthenticationException $exception) {
            return $this->onAuthenticationException($request, $exception);
        }
    }

    /**
     * Callable implementation for the middleware stack.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Psr\Http\Message\ResponseInterface A response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!(new UsersUrl())->checkActionOnRequest('socialLogin', $request)) {
            return $handler->handle($request);
        }

        $service = (new ServiceFactory())->createFromRequest($request);
        if (!$service->isGetUserStep($request)) {
            return (new Response())
                ->withLocation($service->getAuthorizationUrl($request));
        }
        $request = $request->withAttribute(SocialAuthenticator::SOCIAL_SERVICE_ATTRIBUTE, $service);

        return $this->goNext($request, $handler);
    }
}
