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

namespace CakeDC\Users\Controller\Traits;

use CakeDC\Auth\Authentication\AuthenticationService;
use CakeDC\Users\Loader\LoginComponentLoader;
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
     * Social login
     *
     * @throws \Cake\Http\Exception\NotFoundException
     * @return mixed
     */
    public function socialLogin()
    {
        $Login = LoginComponentLoader::forSocial($this);

        return $Login->handleLogin(false, true);
    }

    /**
     * Login user
     *
     * @return mixed
     * @throws \Exception
     */
    public function login()
    {
        $this->getRequest()->getSession()->delete(AuthenticationService::TWO_FACTOR_VERIFY_SESSION_KEY);
        $Login = LoginComponentLoader::forForm($this);

        return $Login->handleLogin(true, false);
    }

    /**
     * Logout
     *
     * @return mixed
     */
    public function logout()
    {
        $user = $this->getRequest()->getAttribute('identity');
        $user = $user ?? [];

        $eventBefore = $this->dispatchEvent(Plugin::EVENT_BEFORE_LOGOUT, ['user' => $user]);
        if (is_array($eventBefore->getResult())) {
            return $this->redirect($eventBefore->getResult());
        }

        $this->getRequest()->getSession()->destroy();
        $this->Flash->success(__d('cake_d_c/users', 'You\'ve successfully logged out'));

        $eventAfter = $this->dispatchEvent(Plugin::EVENT_AFTER_LOGOUT, ['user' => $user]);
        if (is_array($eventAfter->getResult())) {
            return $this->redirect($eventAfter->getResult());
        }

        return $this->redirect($this->Authentication->logout());
    }
}
