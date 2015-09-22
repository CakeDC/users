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
use Cake\Core\Configure;
use Cake\Utility\Hash;

/**
 * Covers the login, logout and social login
 *
 */
trait LoginTrait
{
    use CustomUsersTableTrait;

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

        if (!$this->request->is('post') && !$socialLogin) {
            return;
        }

        try {
            $user = $this->Auth->identify();
            return $this->_afterIdentifyUser($user, $socialLogin);
        } catch (AccountNotActiveException $ex) {
            $socialKey = Configure::read('Users.Key.Session.social');
            $this->request->session()->delete($socialKey);
            $msg = __d('Users', 'Your social account has not been validated yet. Please check your inbox for instructions');
            $this->Flash->success($msg);
        } catch (MissingEmailException $ex) {
            $this->Flash->success(__d('Users', 'Please enter your email'));
            return $this->redirect(['controller' => 'Users', 'action' => 'socialEmail']);
        }
    }

    /**
     * Update remember me and determine redirect url after user identified
     * @param array $user user data after identified
     * @param bool $socialLogin is social login
     * @return array
     */
    protected function _afterIdentifyUser($user, $socialLogin = false)
    {
        $socialKey = Configure::read('Users.Key.Session.social');
        if (!empty($user)) {
            $this->request->session()->delete($socialKey);
            $this->Auth->setUser($user);
            $event = $this->dispatchEvent(UsersAuthComponent::EVENT_AFTER_LOGIN);
            if (is_array($event->result)) {
                return $this->redirect($event->result);
            }
            $url = $this->Auth->redirectUrl();
            return $this->redirect($url);
        } else {
            $message = __d('Users', 'Username or password is incorrect');
            if ($socialLogin) {
                $socialData = $this->request->session()->read($socialKey);
                $socialDataEmail = null;
                if (!empty($socialData->info)) {
                    $socialDataEmail = Hash::get((array)$socialData->info, Configure::read('data_email_key'));
                }
                $postedEmail = $this->request->data(Configure::read('Users.Key.Data.email'));
                if (Configure::read('Users.Email.required') &&
                    empty($socialDataEmail) &&
                    empty($postedEmail)) {
                        return $this->redirect([
                            'controller' => 'Users',
                            'action' => 'socialEmail'
                        ]);
                }
                $message = __d('Users', 'There was an error associating your social network account');
            }
            $this->Flash->error($message, 'default', [], 'auth');
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
