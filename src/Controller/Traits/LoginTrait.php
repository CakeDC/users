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
        $msg = __d('Users', 'Issues trying to log in with your social account');

        if (isset($exception)) {
            if ($exception instanceof MissingEmailException) {
                if ($flash) {
                    $this->Flash->success(__d('Users', 'Please enter your email'));
                }
                $this->request->session()->write(Configure::read('Users.Key.Session.social'), $data);
                return $this->redirect(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'socialEmail']);
            }
            if ($exception instanceof UserNotActiveException) {
                $msg = __d('Users', 'Your user has not been validated yet. Please check your inbox for instructions');
            } elseif ($exception instanceof AccountNotActiveException) {
                $msg = __d('Users', 'Your social account has not been validated yet. Please check your inbox for instructions');
            }
        }
        if ($flash) {
            $this->Auth->config('authError', $msg);
            $this->Auth->config('flash.params', ['class' => 'success']);
            $this->request->session()->delete(Configure::read('Users.Key.Session.social'));
            $this->Flash->success(__d('Users', $msg));
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

        if ($this->request->is('post')) {
            if (!$this->_checkReCaptcha()) {
                $this->Flash->error(__d('Users', 'Invalid reCaptcha'));
                return;
            }
            $user = $this->Auth->identify();
            return $this->_afterIdentifyUser($user, $socialLogin);
        }
        if (!$this->request->is('post') && !$socialLogin) {
            if ($this->Auth->user()) {
                $msg = __d('Users', 'You are already logged in');
                $this->Flash->error($msg);
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
    protected function _afterIdentifyUser($user, $socialLogin = false)
    {
        if (!empty($user)) {
            $this->Auth->setUser($user);

            $event = $this->dispatchEvent(UsersAuthComponent::EVENT_AFTER_LOGIN, ['user' => $user]);
            if (is_array($event->result)) {
                return $this->redirect($event->result);
            }
            $url = $this->Auth->redirectUrl();
            return $this->redirect($url);
        } else {
            if (!$socialLogin) {
                $message = __d('Users', 'Username or password is incorrect');
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
        $this->Flash->success(__d('Users', 'You\'ve successfully logged out'));

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
}
