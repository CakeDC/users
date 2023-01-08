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

namespace CakeDC\Users\Controller\Component;

use Authentication\Authenticator\ResultInterface;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\Log\Log;
use CakeDC\Auth\Authentication\AuthenticationService;
use CakeDC\Auth\Traits\IsAuthorizedTrait;
use CakeDC\Users\Plugin;
use CakeDC\Users\Utility\UsersUrl;
use Laminas\Diactoros\Uri;

/**
 * LoginFailure component
 */
class LoginComponent extends Component
{
    use IsAuthorizedTrait;

    /**
     * @inheritdoc
     */
    protected array $_defaultConfig = [
        'defaultMessage' => null,
        'messages' => [],
        'targetAuthenticator' => null,
    ];

    /**
     * Gets the request instance.
     *
     * @return \Cake\Http\ServerRequest
     */
    public function getRequest(): ServerRequest
    {
        return $this->getController()->getRequest();
    }

    /**
     * Handle login, if success redirect to 'AuthenticationComponent.loginRedirect' or show error
     *
     * @param bool $errorOnlyPost should handle failure only on post request
     * @param bool $redirectFailure should redirect on failure?
     * @return \Cake\Http\Response|null
     */
    public function handleLogin($errorOnlyPost, $redirectFailure)
    {
        $request = $this->getController()->getRequest();
        $service = $request->getAttribute('authentication');
        if (!$service) {
            throw new \UnexpectedValueException('Authentication service not found in this request');
        }
        $eventBefore = $this->getController()->dispatchEvent(Plugin::EVENT_BEFORE_LOGIN, []);
        if (is_array($eventBefore->getResult())) {
            return $this->getController()->redirect($eventBefore->getResult());
        }

        $result = $service->getResult();
        if ($result->isValid()) {
            $user = $request->getAttribute('identity')->getOriginalData();
            $this->handlePasswordRehash($service, $user, $request);
            $this->updateLastLogin($user);

            return $this->afterIdentifyUser($user);
        }
        if ($request->is('post') || $errorOnlyPost === false) {
            $this->getController()->dispatchEvent(Plugin::EVENT_AFTER_LOGIN_FAILURE, ['result' => $result]);

            return $this->handleFailure($redirectFailure);
        }

        return null;
    }

    /**
     * Handle login failure
     *
     * @param bool $redirect should redirect?
     * @return \Cake\Http\Response|null
     */
    public function handleFailure($redirect = true)
    {
        $controller = $this->getController();
        $request = $controller->getRequest();

        $service = $request->getAttribute('authentication');
        $result = $this->getTargetAuthenticatorResult($service);
        $controller->Flash->error($this->getErrorMessage($result), ['element' => 'default', 'key' => 'auth']);

        if (!$redirect) {
            return null;
        }

        return $controller->redirect(UsersUrl::actionUrl('login'));
    }

    /**
     * Get the target authenticator result for current login action
     *
     * @param \CakeDC\Auth\Authentication\AuthenticationService $service authentication service.
     * @return \Authentication\Authenticator\ResultInterface|null
     */
    public function getTargetAuthenticatorResult(AuthenticationService $service)
    {
        $target = $this->getConfig('targetAuthenticator');
        $failures = $service->getFailures();
        foreach ($failures as $failure) {
            if ($failure->getAuthenticator() instanceof $target) {
                return $failure->getResult();
            }
        }

        return null;
    }

    /**
     * Get the error message for result status
     *
     * @param \Authentication\Authenticator\ResultInterface|null $result Result object;
     * @return string
     */
    public function getErrorMessage(?ResultInterface $result = null)
    {
        $messagesMap = $this->getConfig('messages');

        if ($result === null || !isset($messagesMap[$result->getStatus()])) {
            return $this->getConfig('defaultMessage');
        }

        return $messagesMap[$result->getStatus()];
    }

    /**
     * Determine redirect url after user identified
     *
     * @param array $user user data after identified
     * @return \Cake\Http\Response|null
     */
    protected function afterIdentifyUser($user)
    {
        $event = $this->getController()->dispatchEvent(Plugin::EVENT_AFTER_LOGIN, ['user' => $user]);
        if (is_array($event->getResult())) {
            // in this case we don't checkSafeHost the url as the url params are generated by an event
            return $this->getController()->redirect($event->getResult());
        }

        $queryRedirect = $this->getController()->getRequest()->getQuery('redirect');
        $redirectUrl = $this->getController()->Authentication->getConfig('loginRedirect');
        if (!$this->checkSafeHost($queryRedirect)) {
            $userId = $user['id'] ?? null;
            Log::info(
                "Unsafe redirect `$queryRedirect` ignored, user id `{$userId}` " .
                "redirected to `$redirectUrl` after successful login"
            );
            $queryRedirect = $redirectUrl;
        }
        // even if the host is safe, we need to check if the url is authorized for the given user
        // this check ignores the host
        if ($this->isAuthorized($queryRedirect ?? null)) {
            $redirectUrl = $queryRedirect;
        }

        return $this->getController()->redirect($redirectUrl);
    }

    /**
     * Handle password rehash logic
     *
     * @param \CakeDC\Auth\Authentication\AuthenticationService $service Authentication service
     * @param \CakeDC\Users\Model\Entity\User $user User entity.
     * @param \Cake\Http\ServerRequest $request The http request.
     * @return void
     */
    protected function handlePasswordRehash($service, $user, \Cake\Http\ServerRequest $request)
    {
        $indentifiersNames = (array)Configure::read('Auth.PasswordRehash.identifiers');
        foreach ($indentifiersNames as $indentifierName) {
            /**
             * @var \Authentication\Identifier\AbstractIdentifier|null $checker
             */
            $checker = $service->identifiers()->get($indentifierName);
            if (!$checker || method_exists($checker, 'needsPasswordRehash') && !$checker->needsPasswordRehash()) {
                continue;
            }
            $password = $request->getData('password');
            $user->set('password', $password);
            $user->setDirty('modified');
            $this->getController()->getUsersTable()->save($user);
            break;
        }
    }

    /**
     * Check if there is a host defined in the $queryRedirect and it's in the allowed list of hosts
     *
     * @param string|null $queryRedirect redirect url
     * @return bool
     */
    protected function checkSafeHost(?string $queryRedirect = null): bool
    {
        if ($queryRedirect === null) {
            return true;
        }

        $uri = new Uri($queryRedirect);
        $host = $uri->getHost();
        if (!$host) {
            return true;
        }

        return in_array($host, Configure::read('Users.AllowedRedirectHosts'));
    }

    /**
     * Update last loging date
     *
     * @param \CakeDC\Users\Model\Entity\User $user User entity.
     * @return void
     */
    protected function updateLastLogin($user)
    {
        $now = \Cake\I18n\DateTime::now();
        $user->set('last_login', $now);
        $this->getController()->getUsersTable()->updateAll(
            ['last_login' => $now],
            ['id' => $user->id]
        );
    }
}
