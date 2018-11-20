<?php
/**
 * Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Controller\Traits;

use CakeDC\Auth\Authentication\AuthenticationService;
use CakeDC\Users\Plugin;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;

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
     * Social login
     *
     * @throws NotFoundException
     * @return mixed
     */
    public function socialLogin()
    {
        $config = Configure::read('Auth.SocialLoginFailure');
        /**
         * @var \CakeDC\Users\Controller\Component\LoginComponent $Login
         */
        $Login = $this->loadComponent($config['component'], $config);

        return $Login->handleLogin(false, true);
    }

    /**
     * Login user
     *
     * @return mixed
     */
    public function login()
    {
        $this->request->getSession()->delete(AuthenticationService::GOOGLE_VERIFY_SESSION_KEY);
        $config = Configure::read('Auth.FormLoginFailure');
        /**
         * @var \CakeDC\Users\Controller\Component\LoginComponent $Login
         */
        $Login = $this->loadComponent($config['component'], $config);

        return $Login->handleLogin(true, false);
    }

    /**
     * Logout
     *
     * @return mixed
     */
    public function logout()
    {
        $user = $this->request->getAttribute('identity');
        $user = isset($user) ? $user : [];

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
