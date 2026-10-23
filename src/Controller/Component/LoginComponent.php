<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Controller\Component;

use Authentication\Authenticator\MissingIdentifierException;
use Authentication\Authenticator\ResultInterface;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use CakeDC\Auth\Authentication\AuthenticationService;
use CakeDC\Auth\Traits\IsAuthorizedTrait;
use CakeDC\Users\Model\Entity\User;
use CakeDC\Users\UsersPlugin;
use CakeDC\Users\Utility\UsersUrl;
use Laminas\Diactoros\Uri;
use UnexpectedValueException;

/**
 * LoginFailure component
 */
class LoginComponent extends Component
{
    use IsAuthorizedTrait;

    /**
     * @inheritDoc
     */
    protected array $defaultConfig = [
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
    public function handleLogin(bool $errorOnlyPost, bool $redirectFailure): ?Response
    {
        $request = $this->getController()->getRequest();
        $service = $request->getAttribute('authentication');
        if (!$service) {
            throw new UnexpectedValueException('Authentication service not found in this request');
        }
        $eventBefore = $this->getController()->dispatchEvent(UsersPlugin::EVENT_BEFORE_LOGIN, []);
        if (is_array($eventBefore->getResult())) {
            return $this->getController()->redirect($eventBefore->getResult());
        }

        $result = $service->getResult();
        if ($result->isValid()) {
            $user = $request->getAttribute('identity')->getOriginalData();
            $this->handlePasswordRehash($service, $user, $request);
            $this->updateLastLogin($user);
            $this->addFlashMessage($user);

            return $this->afterIdentifyUser($user);
        }
        if ($request->is('post') || $errorOnlyPost === false) {
            $this->getController()->dispatchEvent(UsersPlugin::EVENT_AFTER_LOGIN_FAILURE, ['result' => $result]);

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
    public function handleFailure(bool $redirect = true): ?Response
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
    public function getTargetAuthenticatorResult(AuthenticationService $service): ?ResultInterface
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
    public function getErrorMessage(?ResultInterface $result = null): string
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
     * @param \CakeDC\Users\Model\Entity\User|array $user user data after identified
     * @return \Cake\Http\Response|null
     */
    protected function afterIdentifyUser(User|array $user): ?Response
    {
        $event = $this->getController()->dispatchEvent(UsersPlugin::EVENT_AFTER_LOGIN, ['user' => $user]);
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
                "redirected to `$redirectUrl` after successful login",
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
    protected function handlePasswordRehash(AuthenticationService $service, User $user, ServerRequest $request): void
    {
        // deprecated way to define identifiers via global Auth.Identifiers collection
        // was removed in authentication 5.x (each authenticator owns a single identifier).
        $identifiersNames = (array)Configure::read('Auth.PasswordRehash.identifiers');
        if ($identifiersNames !== []) {
            Log::warning(
                'Auth.PasswordRehash.identifiers is deprecated and no longer supported. ' .
                'Configure Auth.PasswordRehash.authenticators instead.',
            );
        }

        // new way to define identifiers, inside the authenticators
        $authenticatorNames = (array)Configure::read('Auth.PasswordRehash.authenticators');
        if ($authenticatorNames === []) {
            return;
        }

        $authenticationProvider = $service->getAuthenticationProvider();
        if (!$authenticationProvider) {
            return;
        }

        $authenticators = $service->authenticators();
        foreach ($authenticatorNames as $authenticatorName => $identifierName) {
            if ($authenticators->has($authenticatorName) && $authenticators->get($authenticatorName) !== $authenticationProvider) {
                continue;
            }

            try {
                $checker = $authenticationProvider->getIdentifier();
            } catch (MissingIdentifierException) {
                Log::warning(
                    "Error saving user id {$user->get('id')} password after rehashing: " .
                    "identifier $identifierName not found for authenticator $authenticatorName. " .
                    'Check your Auth.PasswordRehash.authenticators configuration.',
                );
                continue;
            }
            $this->saveRehashedPassword($checker, $request, $user);
        }
    }

    /**
     * @param mixed $checker
     * @param \Cake\Http\ServerRequest $request
     * @param \Cake\Datasource\EntityInterface $user
     * @return void
     */
    protected function saveRehashedPassword(mixed $checker, ServerRequest $request, EntityInterface $user): void
    {
        if (!$checker || method_exists($checker, 'needsPasswordRehash') && !$checker->needsPasswordRehash()) {
            return;
        }
        $passwordField = $checker->getConfig('fields.password', 'password');
        $password = $request->getData($passwordField);
        $user->set($passwordField, $password);
        $user->setDirty('modified');
        $userId = $user->get('id');
        if (!method_exists($this->getController(), 'getUsersTable')) {
            Log::warning("Error saving user id $userId password after rehashing: getUsersTable method not found");

            return;
        }
        if (!$this->getController()->getUsersTable()->save($user)) {
            Log::warning("Error saving user id $userId password after rehashing: " . implode(', ', $user->getErrors()));
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
    protected function updateLastLogin(User $user): void
    {
        if (!Configure::read('Users.Login.updateLastLogin', true)) {
            return;
        }
        $field = Configure::read('Users.Login.lastLoginField', 'last_login');
        $now = DateTime::now();
        $user->set($field, $now);
        $this->getController()->getUsersTable()->updateAll(
            [$field => $now->format('Y-m-d H:i:s')],
            ['id' => $user->id],
        );
    }

    /**
     * Add a flash message informing user is logged in
     *
     * @param \CakeDC\Users\Model\Entity\User|array $user user data
     * @return void
     */
    protected function addFlashMessage(User|array $user): void
    {
        if (!Configure::read('Users.Login.flashMessage')) {
            return;
        }

        $this->getController()->Flash->success(__d('cake_d_c/users', 'Welcome, {0}', $user['username']));
    }
}
