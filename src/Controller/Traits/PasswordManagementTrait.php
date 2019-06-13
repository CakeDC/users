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

use Cake\Core\Configure;
use Cake\Validation\Validator;
use CakeDC\Users\Exception\UserNotActiveException;
use CakeDC\Users\Exception\UserNotFoundException;
use CakeDC\Users\Exception\WrongPasswordException;
use CakeDC\Users\Plugin;
use Exception;

/**
 * Covers the password management: reset, change
 *
 * @property \Cake\Http\ServerRequest $request
 */
trait PasswordManagementTrait
{
    use UserValidationTrait;

    /**
     * Change password
     *
     * @return mixed
     */
    public function changePassword()
    {
        $user = $this->getUsersTable()->newEntity([], ['validate' => false]);
        $identity = $this->getRequest()->getAttribute('identity');
        $identity = $identity ?? [];
        $id = $identity['id'] ?? null;

        if (!empty($id)) {
            $user->id = $id;
            $validatePassword = true;
            //@todo add to the documentation: list of routes used
            $redirect = Configure::read('Users.Profile.route');
        } else {
            $user->id = $this->getRequest()->getSession()->read(Configure::read('Users.Key.Session.resetPasswordUserId'));
            $validatePassword = false;
            if (!$user->id) {
                $this->Flash->error(__d('cake_d_c/users', 'User was not found'));
                $this->redirect($this->Authentication->getConfig('loginAction'));

                return;
            }
            //@todo add to the documentation: list of routes used
            $redirect = $this->Authentication->getConfig('loginAction');
        }
        $this->set('validatePassword', $validatePassword);
        if ($this->getRequest()->is(['post', 'put'])) {
            try {
                $validator = $this->getUsersTable()->validationPasswordConfirm(new Validator());
                if (!empty($id)) {
                    $validator = $this->getUsersTable()->validationCurrentPassword($validator);
                }
                $user = $this->getUsersTable()->patchEntity(
                    $user,
                    $this->getRequest()->getData(),
                    ['validate' => $validator]
                );

                if ($user->getErrors()) {
                    $this->Flash->error(__d('cake_d_c/users', 'Password could not be changed'));
                } else {
                    $result = $this->getUsersTable()->changePassword($user);
                    if ($result) {
                        $event = $this->dispatchEvent(Plugin::EVENT_AFTER_CHANGE_PASSWORD, ['user' => $result]);
                        if (!empty($event) && is_array($event->getResult())) {
                            return $this->redirect($event->getResult());
                        }
                        $this->Flash->success(__d('cake_d_c/users', 'Password has been changed successfully'));

                        return $this->redirect($redirect);
                    } else {
                        $this->Flash->error(__d('cake_d_c/users', 'Password could not be changed'));
                    }
                }
            } catch (UserNotFoundException $exception) {
                $this->Flash->error(__d('cake_d_c/users', 'User was not found'));
            } catch (WrongPasswordException $wpe) {
                $this->Flash->error($wpe->getMessage());
            } catch (Exception $exception) {
                $this->Flash->error(__d('cake_d_c/users', 'Password could not be changed'));
                $this->log($exception->getMessage());
            }
        }
        $this->set(compact('user'));
        $this->set('_serialize', ['user']);
    }

    /**
     * Reset password
     *
     * @param null $token token data.
     * @return void
     */
    public function resetPassword($token = null)
    {
        $this->validate('password', $token);
    }

    /**
     * Reset password
     *
     * @return void|\Cake\Http\Response
     */
    public function requestResetPassword()
    {
        $this->set('user', $this->getUsersTable()->newEntity([], ['validate' => false]));
        $this->set('_serialize', ['user']);
        if (!$this->getRequest()->is('post')) {
            return;
        }

        $reference = $this->getRequest()->getData('reference');
        try {
            $resetUser = $this->getUsersTable()->resetToken($reference, [
                'expiration' => Configure::read('Users.Token.expiration'),
                'checkActive' => false,
                'sendEmail' => true,
                'ensureActive' => Configure::read('Users.Registration.ensureActive'),
                'type' => 'password',
            ]);
            if ($resetUser) {
                $msg = __d('cake_d_c/users', 'Please check your email to continue with password reset process');
                $this->Flash->success($msg);
            } else {
                $msg = __d('cake_d_c/users', 'The password token could not be generated. Please try again');
                $this->Flash->error($msg);
            }

            return $this->redirect(['action' => 'login']);
        } catch (UserNotFoundException $exception) {
            $this->Flash->error(__d('cake_d_c/users', 'User {0} was not found', $reference));
        } catch (UserNotActiveException $exception) {
            $this->Flash->error(__d('cake_d_c/users', 'The user is not active'));
        } catch (Exception $exception) {
            $this->Flash->error(__d('cake_d_c/users', 'Token could not be reset'));
            $this->log($exception->getMessage());
        }
    }

    /**
     * resetOneTimePasswordAuthenticator
     *
     * Resets Google Authenticator token by setting secret_verified
     * to false.
     *
     * @param mixed $id of the user record.
     * @return mixed.
     */
    public function resetOneTimePasswordAuthenticator($id = null)
    {
        if ($this->getRequest()->is('post')) {
            try {
                $query = $this->getUsersTable()->query();
                $query->update()
                    ->set(['secret_verified' => false, 'secret' => null])
                    ->where(['id' => $id]);
                $query->execute();

                $message = __d('cake_d_c/users', 'Google Authenticator token was successfully reset');
                $this->Flash->success($message, 'default');
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $this->Flash->error($message, 'default');
            }
        }

        return $this->redirect($this->getRequest()->referer());
    }
}
