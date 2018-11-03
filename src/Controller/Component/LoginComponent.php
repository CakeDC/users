<?php
namespace CakeDC\Users\Controller\Component;

use Authentication\Authenticator\ResultInterface;
use Cake\Controller\Component;
use CakeDC\Users\Authentication\AuthenticationService;
use CakeDC\Users\Plugin;

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
     * @param bool $failureOnlyPost should handle failure only on post request
     * @param bool $redirectFailure should redirect on failure?
     * @return \Cake\Http\Response|null
     */
    public function handleLogin($errorOnlyPost, $redirectFailure)
    {
        $request = $this->getController()->getRequest();
        $result = $request->getAttribute('authentication')->getResult();
        if ($result->isValid()) {
            $user = $request->getAttribute('identity')->getOriginalData();

            return $this->afterIdentifyUser($user);
        }
        if ($request->is('post') || $errorOnlyPost === false) {
            return $this->handleFailure($redirectFailure);
        }
    }

    /**
     * Handle login failure
     *
     * @param boolean $redirect should redirect?
     *
     * @return \Cake\Http\Response|null
     */
    public function handleFailure($redirect = true)
    {
        $controller = $this->getController();
        $request = $controller->getRequest();

        $service = $request->getAttribute('authentication');
        $result = $this->getTargetAuthenticatorResult($service);
        $controller->Flash->error($this->getErrorMessage($result), 'default', [], 'auth');

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

        return $this->getController()->redirect(
            $this->getController()->Authentication->getConfig('loginRedirect')
        );
    }
}
