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

use CakeDC\Users\Auth\TwoFactorAuthenticationCheckerFactory;
use CakeDC\Users\Controller\Component\UsersAuthComponent;
use CakeDC\Users\Exception\AccountNotActiveException;
use CakeDC\Users\Exception\MissingEmailException;
use CakeDC\Users\Exception\UserNotActiveException;
use CakeDC\Users\Model\Table\SocialAccountsTable;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Event\Event;
use Cake\Http\Exception\NotFoundException;
use Cake\Utility\Hash;
use League\OAuth1\Client\Server\Twitter;

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
     * Do twitter login
     *
     * @return mixed
     */
    public function twitterLogin()
    {
        $this->autoRender = false;
        $server = new Twitter([
            'identifier' => Configure::read('OAuth.providers.twitter.options.clientId'),
            'secret' => Configure::read('OAuth.providers.twitter.options.clientSecret'),
            'callback_uri' => Configure::read('OAuth.providers.twitter.options.redirectUri'),
        ]);
        $oauthToken = $this->request->getQuery('oauth_token');
        $oauthVerifier = $this->request->getQuery('oauth_verifier');
        if (!empty($oauthToken) && !empty($oauthVerifier)) {
            $temporaryCredentials = $this->request->getSession()->read('temporary_credentials');
            $tokenCredentials = $server->getTokenCredentials($temporaryCredentials, $oauthToken, $oauthVerifier);
            $user = (array)$server->getUserDetails($tokenCredentials);
            $user['token'] = [
                'accessToken' => $tokenCredentials->getIdentifier(),
                'tokenSecret' => $tokenCredentials->getSecret(),
            ];
            $this->request->getSession()->write(Configure::read('Users.Key.Session.social'), $user);
            try {
                $user = $this->Auth->identify();
                $this->_afterIdentifyUser($user, true);
            } catch (UserNotActiveException $ex) {
                $exception = $ex;
            } catch (AccountNotActiveException $ex) {
                $exception = $ex;
            } catch (MissingEmailException $ex) {
                $exception = $ex;
            }

            if (!empty($exception)) {
                return $this->failedSocialLogin(
                    $exception,
                    $this->request->getSession()->read(Configure::read('Users.Key.Session.social')),
                    true
                );
            }
        } else {
            $temporaryCredentials = $server->getTemporaryCredentials();
            $this->request->getSession()->write('temporary_credentials', $temporaryCredentials);
            $url = $server->getAuthorizationUrl($temporaryCredentials);

            return $this->redirect($url);
        }
    }

    /**
     * @param Event $event event
     * @return mixed
     */
    public function failedSocialLoginListener(Event $event)
    {
        return $this->failedSocialLogin($event->data['exception'], $event->data['rawData'], true);
    }

    /**
     * @param mixed $exception exception
     * @param mixed $data data
     * @param bool|false $flash flash
     * @return mixed
     */
    public function failedSocialLogin($exception, $data, $flash = false)
    {
        $msg = __d('CakeDC/Users', 'Issues trying to log in with your social account');

        if (isset($exception)) {
            if ($exception instanceof MissingEmailException) {
                if ($flash) {
                    $this->Flash->success(__d('CakeDC/Users', 'Please enter your email'), ['clear' => true]);
                }
                $this->request->getSession()->write(Configure::read('Users.Key.Session.social'), $data);

                return $this->redirect([
                    'plugin' => 'CakeDC/Users',
                    'controller' => 'Users',
                    'action' => 'socialEmail'
                ]);
            }
            if ($exception instanceof UserNotActiveException) {
                $msg = __d(
                    'CakeDC/Users',
                    'Your user has not been validated yet. Please check your inbox for instructions'
                );
            } elseif ($exception instanceof AccountNotActiveException) {
                $msg = __d(
                    'CakeDC/Users',
                    'Your social account has not been validated yet. Please check your inbox for instructions'
                );
            }
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
     * @return array
     */
    public function socialLogin()
    {
        $socialProvider = $this->request->getParam('provider');
        $socialUser = $this->request->getSession()->read(Configure::read('Users.Key.Session.social'));

        if (empty($socialProvider) && empty($socialUser)) {
            throw new NotFoundException();
        }
        $user = $this->Auth->user();

        return $this->_afterIdentifyUser($user, true);
    }

    /**
     * Login user
     *
     * @return mixed
     */
    public function login()
    {
        $event = $this->dispatchEvent(UsersAuthComponent::EVENT_BEFORE_LOGIN);
        if (is_array($event->result)) {
            return $this->_afterIdentifyUser($event->result);
        }
        if ($event->isStopped()) {
            return $this->redirect($event->result);
        }

        $socialLogin = $this->_isSocialLogin();

        if ($this->request->is('post')) {
            if (!$this->_checkReCaptcha()) {
                $this->Flash->error(__d('CakeDC/Users', 'Invalid reCaptcha'));

                return;
            }
            $user = $this->Auth->identify();

            return $this->_afterIdentifyUser(
                $user,
                $socialLogin,
                $user && $this->getTwoFactorAuthenticationChecker()->isRequired($user)
            );
        }

        if (!$this->request->is('post') && !$socialLogin) {
            if ($this->Auth->user()) {
                if (!$this->request->getSession()->read('Users.successSocialLogin')) {
                    $msg = __d('CakeDC/Users', 'You are already logged in');
                    $this->Flash->error($msg);
                } else {
                    $this->request->getSession()->delete('Users.successSocialLogin');
                    $this->request->getSession()->delete('Flash');
                }
                $url = $this->Auth->redirectUrl();

                return $this->redirect($url);
            }
        }
    }

    /**
     * Verify for Google Authenticator
     * If Google Authenticator's enabled we need to verify
     * authenticated user. To avoid accidental access to
     * other URL's we store auth'ed used into temporary session
     * to perform code verification.
     *
     * @return mixed
     */
    public function verify()
    {
        $loginUrl = array_merge(
            Configure::read('Auth.loginAction'),
            [
                '?' => $this->request->getQueryParams()
            ]
        );
        if (!$this->getTwoFactorAuthenticationChecker()->isEnabled()) {
            $message = __d('CakeDC/Users', 'Please enable Google Authenticator first.');
            $this->Flash->error($message, 'default', [], 'auth');

            return $this->redirect($loginUrl);
        }

        $temporarySession = $this->request->getSession()->read('temporarySession');
        if (!is_array($temporarySession) || empty($temporarySession)) {
            $this->Flash->error(__d('CakeDC/Users', 'Invalid request.'), 'default', [], 'auth');

            return $this->redirect($loginUrl);
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

                    return $this->redirect($loginUrl);
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
                $event = $this->dispatchEvent(UsersAuthComponent::EVENT_AFTER_LOGIN, ['user' => $user]);
                if (is_array($event->result)) {
                    return $this->redirect($event->result);
                }
                $url = $this->Auth->redirectUrl();

                return $this->redirect($url);
            } else {
                $this->request->getSession()->destroy();
                $message = __d('CakeDC/Users', 'Verification code is invalid. Try again');
                $this->Flash->error($message, 'default', [], 'auth');

                return $this->redirect($loginUrl);
            }
        }
    }

    /**
     * Check reCaptcha if enabled for login
     *
     * @return bool
     */
    protected function _checkReCaptcha()
    {
        if (!Configure::read('Users.reCaptcha.login')) {
            return true;
        }

        return $this->validateReCaptcha(
            $this->request->getData('g-recaptcha-response'),
            $this->request->clientIp()
        );
    }

    /**
     * Update remember me and determine redirect url after user identified
     * @param array $user user data after identified
     * @param bool $socialLogin is social login
     * @param bool $googleAuthenticatorLogin googleAuthenticatorLogin
     * @return array
     */
    protected function _afterIdentifyUser($user, $socialLogin = false, $googleAuthenticatorLogin = false)
    {
        if (!empty($user)) {
            if ($googleAuthenticatorLogin) {
                // storing user's session in the temporary one
                // until the GA verification is checked
                $this->request->getSession()->write('temporarySession', $user);
                $url = Configure::read('GoogleAuthenticator.verifyAction');
                $url = array_merge($url, [
                    '?' => $this->request->getQueryParams()
                ]);

                return $this->redirect($url);
            }

            $this->Auth->setUser($user);
            $event = $this->dispatchEvent(UsersAuthComponent::EVENT_AFTER_LOGIN, ['user' => $user]);
            if (is_array($event->result)) {
                return $this->redirect($event->result);
            }

            $url = $this->Auth->redirectUrl();

            return $this->redirect($url);
        } else {
            if (!$socialLogin) {
                $message = __d('CakeDC/Users', 'Username or password is incorrect');
                $this->Flash->error($message, 'default', [], 'auth');
            }

            return $this->redirect(Configure::read('Auth.loginAction'));
        }
    }

    /**
     * Logout
     *
     * @return mixed
     */
    public function logout()
    {
        $user = (array)$this->Auth->user();

        $eventBefore = $this->dispatchEvent(UsersAuthComponent::EVENT_BEFORE_LOGOUT, ['user' => $user]);
        if (is_array($eventBefore->result)) {
            return $this->redirect($eventBefore->result);
        }

        $this->request->getSession()->destroy();
        $this->Flash->success(__d('CakeDC/Users', 'You\'ve successfully logged out'));

        $eventAfter = $this->dispatchEvent(UsersAuthComponent::EVENT_AFTER_LOGOUT, ['user' => $user]);
        if (is_array($eventAfter->result)) {
            return $this->redirect($eventAfter->result);
        }

        return $this->redirect($this->Auth->logout());
    }

    /**
     * Check if we are doing a social login
     *
     * @return bool true if social login is enabled and we are processing the social login
     * data in the request
     */
    protected function _isSocialLogin()
    {
        return Configure::read('Users.Social.login') &&
            $this->request->getSession()->check(Configure::read('Users.Key.Session.social'));
    }

    /**
     * Get the configured two factory authentication
     *
     * @return \CakeDC\Users\Auth\TwoFactorAuthenticationCheckerInterface
     */
    protected function getTwoFactorAuthenticationChecker()
    {
        return (new TwoFactorAuthenticationCheckerFactory())->build();
    }
}
