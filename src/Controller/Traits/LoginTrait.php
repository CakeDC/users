<?php
/**
 * Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Controller\Traits;

use Authentication\AuthenticationService;
use Authentication\Authenticator\Result;
use CakeDC\Users\Authenticator\AuthenticatorFeedbackInterface;
use CakeDC\Users\Authenticator\FormAuthenticator;
use CakeDC\Users\Middleware\SocialAuthMiddleware;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use CakeDC\Users\Plugin;

/**
 * Covers the login, logout and social login
 *
 * @property \Cake\Controller\Component\AuthComponent $Auth
 * @property \Cake\Http\ServerRequest $request
 */
trait LoginTrait
{
    use CustomUsersTableTrait;

    /**
     * @param int $error auth error
     * @param mixed $data data
     * @param bool|false $flash flash
     * @return mixed
     */
    public function failedSocialLogin($error, $data, $flash = false)
    {
        $msg = __d('CakeDC/Users', 'Issues trying to log in with your social account');

        switch ($error) {
            case SocialAuthMiddleware::AUTH_ERROR_MISSING_EMAIL:
                if ($flash) {
                    $this->Flash->success(__d('CakeDC/Users', 'Please enter your email'), ['clear' => true]);
                }
                $this->request->getSession()->write(Configure::read('Users.Key.Session.social'), $data);

                return $this->redirect([
                    'plugin' => 'CakeDC/Users',
                    'controller' => 'Users',
                    'action' => 'socialEmail'
                ]);
            case SocialAuthMiddleware::AUTH_ERROR_USER_NOT_ACTIVE:
                $msg = __d(
                    'CakeDC/Users',
                    'Your user has not been validated yet. Please check your inbox for instructions'
                );
                break;
            case SocialAuthMiddleware::AUTH_ERROR_ACCOUNT_NOT_ACTIVE:
                $msg = __d(
                    'CakeDC/Users',
                    'Your social account has not been validated yet. Please check your inbox for instructions'
                );
                break;

        }

        if ($flash) {
            $this->request->getSession()->delete(Configure::read('Users.Key.Session.social'));
            $this->Flash->success($msg, ['clear' => true]);
        }

        return $this->redirect(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login']);
    }

    /**
     * Social login
     *
     * @throws NotFoundException
     * @return mixed
     */
    public function socialLogin()
    {
        $status = $this->request->getAttribute('socialAuthStatus');
        if ($status === SocialAuthMiddleware::AUTH_SUCCESS) {
            $user = $this->request->getAttribute('identity')->getOriginalData();

            return $this->_afterIdentifyUser($user);
        }
        $socialProvider = $this->request->getParam('provider');

        if (empty($socialProvider)) {
            throw new NotFoundException();
        }

        $data = $this->request->getAttribute('socialRawData');

        return $this->failedSocialLogin($status, $data);
    }

    /**
     * Login user
     *
     * @return mixed
     */
    public function login()
    {
        $this->request->getSession()->delete('temporarySession');
        $result = $this->request->getAttribute('authentication')->getResult();

        if ($result->isValid()) {
            $user = $this->request->getAttribute('identity')->getOriginalData();

            return $this->_afterIdentifyUser($user);
        }

        $service = $this->request->getAttribute('authentication');
        $message = $this->_getLoginErrorMessage($service);

        if (empty($message) && $this->request->is('post')) {
            $message = __d('CakeDC/Users', 'Username or password is incorrect');
        }

        if ($message) {
            $this->Flash->error($message, 'default', [], 'auth');
        }
    }

    /**
     * Get the list of login error message map by status
     *
     * @return array
     */
    protected function _getLoginErrorMessageMap()
    {
        if (!Configure::read('Users.GoogleAuthenticator.login')) {
            $message = __d('CakeDC/Users', 'Please enable Google Authenticator first.');
            $this->Flash->error($message, 'default', [], 'auth');

            return $this->redirect(Configure::read('Auth.loginAction'));
        }

        $temporarySession = $this->request->getSession()->read('temporarySession');
        if (!is_array($temporarySession) || empty($temporarySession)) {
            $this->Flash->error(__d('CakeDC/Users', 'Invalid request.'), 'default', [], 'auth');

            return $this->redirect(Configure::read('Auth.loginAction'));
        }

        $secret = Hash::get($temporarySession, 'secret');
        $secretVerified = Hash::get($temporarySession, 'secret_verified');

        // showing QR-code until shared secret is verified
        if (!$secretVerified) {
            if (empty($secret)) {
                $secret = $this->GoogleAuthenticator->createSecret();

                // catching sql exception in case of any sql inconsistencies
                try {
                    $query = $this->getUsersTable()->query();
                    $query->update()
                        ->set(['secret' => $secret])
                        ->where(['id' => $temporarySession['id']]);
                    $query->execute();

                    $this->request->getSession()->write('temporarySession.secret', $secret);
                } catch (\Exception $e) {
                    $this->request->getSession()->destroy();
                    $message = $e->getMessage();
                    $this->Flash->error($message, 'default', [], 'auth');

                    return $this->redirect(Configure::read('Auth.loginAction'));
                }
            }
            $secretDataUri = $this->GoogleAuthenticator->getQRCodeImageAsDataUri(
                Hash::get((array)$temporarySession, 'email'),
                $secret
            );
            $this->set(compact('secretDataUri'));
        }

        if ($this->request->is('post')) {
            $codeVerified = false;
            $verificationCode = $this->request->getData('code');
            $user = $this->request->getSession()->read('temporarySession');
            $entity = $this->getUsersTable()->get($user['id']);

            if (!empty($entity['secret'])) {
                $codeVerified = $this->GoogleAuthenticator->verifyCode($entity['secret'], $verificationCode);
            }

            if ($codeVerified) {
                unset($user['secret']);

                if (!$user['secret_verified']) {
                    $this->getUsersTable()->query()->update()
                        ->set(['secret_verified' => true])
                        ->where(['id' => $user['id']])
                        ->execute();

                    $user['secret_verified'] = true;
                }

                $this->request->getSession()->delete('temporarySession');
                $this->Auth->setUser($user);
                $url = $this->Auth->redirectUrl();

                return $this->redirect($url);
            } else {
                $this->request->getSession()->destroy();
                $message = __d('CakeDC/Users', 'Verification code is invalid. Try again');
                $this->Flash->error($message, 'default', [], 'auth');

                return $this->redirect(Configure::read('Auth.loginAction'));
            }
        }
    }

    /**
     * Show the login error message based on authenticators
     *
     * @param AuthenticationService $service authentication service used in request
     *
     * @return string
     */
    protected function _getLoginErrorMessage(AuthenticationService $service)
    {
        $message = '';
        $errorMessages = $this->_getLoginErrorMessageMap();
        foreach ($service->authenticators() as $key => $authenticator) {
            if (!$authenticator instanceof AuthenticatorFeedbackInterface) {
                continue;
            }

            $result = $authenticator->getLastResult();
            $status = $result ? $result->getStatus() : null;

            if ($status && isset($errorMessages[$status])) {
                $message = $errorMessages[$status];
            }
        }

        return $message;
    }

    /**
     * Determine redirect url after user identified
     *
     * @param array $user user data after identified
     * @return array
     */
    protected function _afterIdentifyUser($user)
    {
        if (!empty($user)) {
            if ($googleAuthenticatorLogin) {
                // storing user's session in the temporary one
                // until the GA verification is checked
                $this->request->getSession()->write('temporarySession', $user);
                $url = Configure::read('GoogleAuthenticator.verifyAction');

                return $this->redirect($url);
            }

            $this->Auth->setUser($user);
            $event = $this->dispatchEvent(UsersAuthComponent::EVENT_AFTER_LOGIN, ['user' => $user]);
            if (is_array($event->result)) {
                return $this->redirect($event->result);
            }

            return $this->redirect($this->Authentication->getConfig('loginRedirect'));
        } else {
            if (!$socialLogin) {
                $message = __d('CakeDC/Users', 'Username or password is incorrect');
                $this->Flash->error($message, 'default', [], 'auth');
            }

            return $this->redirect($this->Authentication->getConfig('loginAction'));
        }

        return $this->redirect($this->Authentication->getConfig('loginRedirect'));
    }

    /**
     * Logout
     *
     * @return mixed
     */
    public function logout()
    {
        $user = $this->request->getAttribute('identity') ?? [];

        $eventBefore = $this->dispatchEvent(Plugin::EVENT_BEFORE_LOGOUT, ['user' => $user]);
        if (is_array($eventBefore->result)) {
            return $this->redirect($eventBefore->result);
        }

        $this->request->getSession()->destroy();
        $this->Flash->success(__d('CakeDC/Users', 'You\'ve successfully logged out'));

        $eventAfter = $this->dispatchEvent(Plugin::EVENT_AFTER_LOGOUT, ['user' => $user]);
        if (is_array($eventAfter->result)) {
            return $this->redirect($eventAfter->result);
        }

        return $this->redirect($this->Authentication->logout());
    }
}
