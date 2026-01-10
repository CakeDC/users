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

namespace CakeDC\Users\Controller\Traits;

use Cake\Datasource\Exception\RecordNotFoundException;
use CakeDC\Users\Utility\UsersUrl;

/**
 * Covers the login, logout and social login
 *
 * @property \Cake\Http\ServerRequest $request
 */
trait OneTimeTokenTrait
{
    /**
     * Request a single token login link.
     *
     * @return \Cake\Http\Response|null
     */
    public function requestLoginLink()
    {
        if ($this->getRequest()->is('post')) {
            $email = $this->getRequest()->getData('email');
            try {
                /** @var \CakeDC\Users\Model\Table\UsersTable $Users */
                $Users = $this->getUsersTable();
                /** @uses \CakeDC\Users\Model\Behavior\OneTimeLoginLinkBehavior::sendLoginLink() */
                $Users->sendLoginLink($email);
            } catch (RecordNotFoundException $e) {
                $this->log(
                    sprintf('A user is trying to get a login link for the email %s but it does not exist.', $email),
                );
            }
            $msg = __d(
                'cake_d_c/users',
                'If your user is registered in the system you will receive an email ' .
                'with a link so you can access your user area.',
            );
            $this->Flash->success($msg);
            $this->setRequest($this->getRequest()->withoutData('email'));

            return $this->redirect(UsersUrl::actionUrl('login'));
        }

        return null;
    }

    /**
     * Single token login.
     *
     * @return \Cake\Http\Response|null
     */
    public function singleTokenLogin()
    {
        $errorMessage = null;
        $token = null;
        if ($this->getRequest()->is('get')) {
            $token = $this->getRequest()->getQuery('token');
        }

        if ($this->getRequest()->is('post') || $token) {
            $user = $this->Authentication->getIdentity();
            $token = $this->getRequest()->getData('token', $token);
            if (is_array($token)) {
                $token = join($token);
            }
            if (!$user && !empty($token)) {
                $errorMessage = __d('cake_d_c/users', 'Invalid or expired token. Please request a new one.');
            }
        }

        if ($errorMessage) {
            $this->Flash->error($errorMessage);

            return $this->redirect(UsersUrl::actionUrl('login'));
        }

        return $this->redirect('/');
    }
}
