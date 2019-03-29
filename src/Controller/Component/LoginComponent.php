<?php
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
use Cake\Core\Configure;
use CakeDC\Auth\Authentication\AuthenticationService;
use CakeDC\Users\Plugin;
use Cake\Controller\Component;

/**
 * LoginFailure component
 */
class LoginComponent extends Component
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'defaultMessage' => null,
        'messages' => [],
        'targetAuthenticator' => null,
    ];

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
        $result = $service->getResult();
        if ($result->isValid()) {
            $user = $request->getAttribute('identity')->getOriginalData();
            $this->handlePasswordRehash($service, $user, $request);

            return $this->afterIdentifyUser($user);
        }
        if ($request->is('post') || $errorOnlyPost === false) {
            return $this->handleFailure($redirectFailure);
        }
    }

    /**
     * Handle login failure
     *
     * @param bool $redirect should redirect?
     *
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

        return $controller->redirect(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login']);
    }

    /**
     * Get the target authenticator result for current login action
     *
     * @param AuthenticationService $service authentication service.
     * @return ResultInterface|null
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
     * @param ResultInterface|null $result Result object;
     * @return string
     */
    public function getErrorMessage(ResultInterface $result = null)
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
     * @return \Cake\Http\Response
     */
    protected function afterIdentifyUser($user)
    {
        $event = $this->getController()->dispatchEvent(Plugin::EVENT_AFTER_LOGIN, ['user' => $user]);
        if (is_array($event->result)) {
            return $this->getController()->redirect($event->result);
        }

        $query = $this->getController()->request->getQueryParams();
        $redirectUrl = $this->getController()->Authentication->getConfig('loginRedirect');
        if (isset($query['redirect'])) {
            $redirectUrl = $query['redirect'];
        }

        return $this->getController()->redirect($redirectUrl);
    }

    /**
     * Handle password rehash logic
     *
     * @param \CakeDC\Auth\Authentication\AuthenticationService $service Authentication service
     * @param \CakeDC\Users\Model\Entity\User $user User entity.
     * @param \Cake\Http\ServerRequest $request The http request.
     *
     * @return void
     */
    protected function handlePasswordRehash($service, $user, \Cake\Http\ServerRequest $request)
    {
        $indentifiersNames = (array)Configure::read('Auth.PasswordRehash.identifiers');
        foreach ($indentifiersNames as $indentifierName) {
            /**
             * @var \Authentication\PasswordHasher\PasswordHasherTrait $checker |null
             */
            $checker = $service->identifiers()->get($indentifierName);
            if (!$checker || !$checker->needsPasswordRehash()) {
                continue;
            }
            $password = $request->getData('password');
            $user->set('password', $password);
            $user->setDirty('modified');
            $this->getController()->getUsersTable()->save($user);
            break;
        }
    }
}
