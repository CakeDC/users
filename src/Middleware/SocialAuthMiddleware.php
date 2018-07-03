<?php

namespace CakeDC\Users\Middleware;

use Cake\Core\InstanceConfigTrait;
use CakeDC\Users\Exception\AccountNotActiveException;
use CakeDC\Users\Exception\MissingEmailException;
use CakeDC\Users\Exception\UserNotActiveException;
use Cake\Core\Configure;
use Cake\Event\EventDispatcherTrait;
use Cake\Http\ServerRequest;
use Cake\Log\LogTrait;
use CakeDC\Users\Plugin;
use CakeDC\Users\Social\Locator\DatabaseLocator;
use CakeDC\Users\Social\Service\ServiceFactory;
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

    protected $_defaultConfig = [];
    protected $authStatus = 0;
    protected $rawData = [];

    /**
     * @var \CakeDC\Users\Social\Service\ServiceInterface
     */
    protected $service;

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

        $this->setConfig(Configure::read('SocialAuthMiddleware'));

        $this->service = (new ServiceFactory())->createFromRequest($request);
        if (!$this->service->isGetUserStep($request)) {
            return $response->withLocation($this->service->getAuthorizationUrl($request));
        }

        return $this->finishWithResult($this->authenticate($request), $request, $response, $next);
    }

    /**
     * finish middleware process.
     *
     * @param int $result authentication result
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @param callable $next Callback to invoke the next middleware.
     * @return \Psr\Http\Message\ResponseInterface A response
     */
    protected function finishWithResult($result, ServerRequest $request, ResponseInterface $response, $next)
    {
        if ($result) {
            $this->authStatus = self::AUTH_SUCCESS;
            $request->getSession()->write(
                $this->getConfig('sessionAuthKey'),
                $result
            );

            $request->getSession()->delete(Configure::read('Users.Key.Session.social'));
            $request->getSession()->write('Users.successSocialLogin', true);
        }

        $request = $request->withAttribute('socialAuthStatus', $this->authStatus);
        $request = $request->withAttribute('socialRawData', $this->rawData);

        return $next($request, $response);
    }

    /**
     * Get a user based on information in the request.
     *
     * @param \Cake\Http\ServerRequest $request Request object.
     * @param \Cake\Http\Response $response Response object
     * @return bool
     * @throws \RuntimeException If the `CakeDC/Users/OAuth2.newUser` event is missing or returns empty.
     */
    protected function authenticate(ServerRequest $request)
    {
        $user = $this->getUser($request);
        if (!$user) {
            return false;
        }

        $this->rawData = $user;

        return $this->_touch($user);
    }

    /**
     * Authenticates with OAuth provider by getting an access token and
     * retrieving the authorized user's profile data.
     *
     * @param \Cake\Http\ServerRequest $request Request object.
     * @return array|bool
     */
    protected function getUser(ServerRequest $request)
    {
        try {
            $rawData = $this->service->getUser($request);

            return $this->_mapUser($rawData);
        } catch (\Exception $e) {
            $message = sprintf(
                "Error getting an access token / retrieving the authorized user's profile data. Error message: %s %s",
                $e->getMessage(),
                $e
            );
            $this->log($message);

            return false;
        }
    }

    /**
     * Find or create local user
     *
     * @param array $data data
     * @return array|bool|mixed
     * @throws MissingEmailException
     */
    protected function _touch(array $data)
    {
        $locator = new DatabaseLocator($this->getConfig('locator'));
        try {
            return $locator->getOrCreate($data);
        } catch (UserNotActiveException $ex) {
            $this->authStatus = self::AUTH_ERROR_USER_NOT_ACTIVE;
            $exception = $ex;
        } catch (AccountNotActiveException $ex) {
            $this->authStatus = self::AUTH_ERROR_ACCOUNT_NOT_ACTIVE;
            $exception = $ex;
        } catch (MissingEmailException $ex) {
            $this->authStatus = self::AUTH_ERROR_MISSING_EMAIL;
            $exception = $ex;
        } catch(RecordNotFoundException $ex) {
            $this->authStatus = self::AUTH_ERROR_FIND_USER;
            $exception = $ex;
        }

        $args = ['exception' => $exception, 'rawData' => $data];
        $this->dispatchEvent(Plugin::EVENT_FAILED_SOCIAL_LOGIN, $args);

        return false;
    }

    /**
     * Map userdata with mapper defined at $providerConfig
     *
     * @param array $data User data
     * @return mixed Either false or an array of user information
     */
    protected function _mapUser($data)
    {
        $providerMapperClass = $this->service->getConfig('mapper');
        $providerMapper = new $providerMapperClass($data);
        $user = $providerMapper();
        $user['provider'] = $this->service->getProviderName();

        return $user;
    }
}