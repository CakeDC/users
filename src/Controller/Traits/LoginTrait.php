<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Controller\Traits;

use CakeDC\Users\Controller\Component\UsersAuthComponent;
use CakeDC\Users\Exception\AccountNotActiveException;
use CakeDC\Users\Exception\MissingEmailException;
use CakeDC\Users\Exception\UserNotActiveException;
use CakeDC\Users\Model\Table\SocialAccountsTable;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\TableRegistry;
use League\OAuth1\Client\Server\Twitter;

/**
 * Covers the login, logout and social login
 *
 */
trait LoginTrait
{
    use CustomUsersTableTrait;

    /**
     * Do twitter login
     *
     * @return mixed|void
     */
    public function twitterLogin()
    {
        $this->autoRender = false;
        $server = new Twitter([
            'identifier' => Configure::read('OAuth.providers.twitter.options.clientId'),
            'secret' => Configure::read('OAuth.providers.twitter.options.clientSecret'),
            'callbackUri' => Configure::read('OAuth.providers.twitter.options.redirectUri'),
        ]);
        $oauthToken = $this->request->query('oauth_token');
        $oauthVerifier = $this->request->query('oauth_verifier');
        if (!empty($oauthToken) && !empty($oauthVerifier)) {
            $temporaryCredentials = $this->request->session()->read('temporary_credentials');
            $tokenCredentials = $server->getTokenCredentials($temporaryCredentials, $oauthToken, $oauthVerifier);
            $user = (array)$server->getUserDetails($tokenCredentials);
            $user['token'] = [
                'accessToken' => $tokenCredentials->getIdentifier(),
                'tokenSecret' => $tokenCredentials->getSecret(),
            ];
            $this->request->session()->write(Configure::read('Users.Key.Session.social'), $user);
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
                return $this->failedSocialLogin($exception, $this->request->session()->read(Configure::read('Users.Key.Session.social')), true);
            }
        } else {
            $temporaryCredentials = $server->getTemporaryCredentials();
            $this->request->session()->write('temporary_credentials', $temporaryCredentials);
            $url = $server->getAuthorizationUrl($temporaryCredentials);

            return $this->redirect($url);
        }
    }

    /**
     * @param Event $event event
     * @return void
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
                    $this->Flash->success(__d('CakeDC/Users', 'Please enter your email'));
                }
                $this->request->session()->write(Configure::read('Users.Key.Session.social'), $data);

                return $this->redirect(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'socialEmail']);
            }
            if ($exception instanceof UserNotActiveException) {
                $msg = __d('CakeDC/Users', 'Your user has not been validated yet. Please check your inbox for instructions');
            } elseif ($exception instanceof AccountNotActiveException) {
                $msg = __d('CakeDC/Users', 'Your social account has not been validated yet. Please check your inbox for instructions');
            }
        }
        if ($flash) {
            $this->Auth->config('authError', $msg);
            $this->Auth->config('flash.params', ['class' => 'success']);
            $this->request->session()->delete(Configure::read('Users.Key.Session.social'));
            $this->Flash->success(__d('CakeDC/Users', $msg));
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
        $socialProvider = $this->request->param('provider');
        $socialUser = $this->request->session()->read(Configure::read('Users.Key.Session.social'));

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
        $googleAuthenticatorLogin = $this->_isGoogleAuthenticator();

        if ($this->request->is('post')) {
            if (!$this->_checkReCaptcha()) {
                $this->Flash->error(__d('CakeDC/Users', 'Invalid reCaptcha'));

                return;
            }
            $user = $this->Auth->identify();

            return $this->_afterIdentifyUser($user, $socialLogin, $googleAuthenticatorLogin);
        }

        if (!$this->request->is('post') && !$socialLogin) {
            if ($this->Auth->user()) {
                $msg = __d('CakeDC/Users', 'You are already logged in');
                $this->Flash->error($msg);
                $url = $this->Auth->redirectUrl();

                return $this->redirect($url);
            }
        }
    }

    /**
     * verify for Google Authenticator codes
     */
    public function verify()
    {
        if (!Configure::read('Users.GoogleAuthenticator.login')) {
            $message = __d('CakeDC/Users', 'Google Authenticator is disabled');
            $this->Flash->error($message, 'default', [], 'auth');

            $this->redirect(Configure::read('Auth.loginAction'));
        }

        $user = $this->Auth->user();
        $secret = $user['secret'];
        if (empty($secret)) {
            $secret = $this->GoogleAuthenticator->createSecret();

            $users = TableRegistry::get('Users');
            $query = $users->query();
            $query->update()
                ->set(['secret' => $secret])
                ->where(['id' => $user['id']])
                ->execute();

            $this->request->session()->write('Auth.User.secret', $secret);
            $this->set('secretDataUri', $this->GoogleAuthenticator->getQRCodeImageAsDataUri($user['email'], $secret));
        }

        if ($this->request->is('post')) {
            $verificationCode = $this->request->data['code'];
            $user = $this->Auth->user();

            $verified = $this->GoogleAuthenticator->verifyCode($secret, $verificationCode);

            if (!$verified) {
                $message = __d('CakeDC/Users', 'Verification code is invalid. Try again');
                $this->Flash->error($message, 'default', [], 'auth');

                //prevent the user accessing the authorized area
                //with unverified two-step, thus destroying the session
                $this->request->session()->destroy();

                $this->redirect(Configure::read('Auth.loginAction'));
            } else {
                //removing secret key from the session, once verified
                $this->request->session()->delete('Auth.User.secret');
                $url = $this->Auth->redirectUrl();

                return $this->redirect($url);
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
            $this->request->data('g-recaptcha-response'),
            $this->request->clientIp()
        );
    }

    /**
     * Update remember me and determine redirect url after user identified
     * @param array $user user data after identified
     * @param bool $socialLogin is social login
     * @return array
     */
    protected function _afterIdentifyUser($user, $socialLogin = false, $googleAuthenticatorLogin = false)
    {
        if (!empty($user)) {
            $this->Auth->setUser($user);

            if ($googleAuthenticatorLogin) {
                $url = Configure::read('GoogleAuthenticator.verifyAction');

                return $this->redirect($url);
            }

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
     * @return type
     */
    public function logout()
    {
        $eventBefore = $this->dispatchEvent(UsersAuthComponent::EVENT_BEFORE_LOGOUT);
        if (is_array($eventBefore->result)) {
            return $this->redirect($eventBefore->result);
        }

        $this->request->session()->destroy();
        $this->Flash->success(__d('CakeDC/Users', 'You\'ve successfully logged out'));

        $eventAfter = $this->dispatchEvent(UsersAuthComponent::EVENT_AFTER_LOGOUT);
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
                $this->request->session()->check(Configure::read('Users.Key.Session.social'));
    }

    /**
     * Check if we doing Google Authenticator Two Factor auth
     * @return bool true if Google Authenticator is enabled
     */
    protected function _isGoogleAuthenticator()
    {
        return Configure::read('Users.GoogleAuthenticator.login');
    }
}
