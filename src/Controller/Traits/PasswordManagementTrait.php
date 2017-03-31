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

use CakeDC\Users\Exception\UserNotActiveException;
use CakeDC\Users\Exception\UserNotFoundException;
use CakeDC\Users\Exception\WrongPasswordException;
use Cake\Core\Configure;
use Cake\Validation\Validator;
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
        $user = $this->getUsersTable()->newEntity();
        $id = $this->Auth->user('id');
        if (!empty($id)) {
            $user->id = $this->Auth->user('id');
            $validatePassword = true;
            //@todo add to the documentation: list of routes used
            $redirect = Configure::read('Users.Profile.route');
        } else {
            $user->id = $this->request->session()->read(Configure::read('Users.Key.Session.resetPasswordUserId'));
            $validatePassword = false;
            if (!$user->id) {
                $this->Flash->error(__d('CakeDC/Users', 'User was not found'));
                $this->redirect($this->Auth->config('loginAction'));

                return;
            }
            //@todo add to the documentation: list of routes used
            $redirect = $this->Auth->config('loginAction');
        }
        $this->set('validatePassword', $validatePassword);
        if ($this->request->is('post')) {
            try {
                $validator = $this->getUsersTable()->validationPasswordConfirm(new Validator());
                if (!empty($id)) {
                    $validator = $this->getUsersTable()->validationCurrentPassword($validator);
                }
                $user = $this->getUsersTable()->patchEntity(
                    $user,
                    $this->request->getData(),
                    ['validate' => $validator]
                );
                if ($user->errors()) {
                    $this->Flash->error(__d('CakeDC/Users', 'Password could not be changed'));
                } else {
                    $user = $this->getUsersTable()->changePassword($user);
                    if ($user) {
                        $this->Flash->success(__d('CakeDC/Users', 'Password has been changed successfully'));

                        return $this->redirect($redirect);
                    } else {
                        $this->Flash->error(__d('CakeDC/Users', 'Password could not be changed'));
                    }
                }
            } catch (UserNotFoundException $exception) {
                $this->Flash->error(__d('CakeDC/Users', 'User was not found'));
            } catch (WrongPasswordException $wpe) {
                $this->Flash->error(__d('CakeDC/Users', '{0}', $wpe->getMessage()));
            } catch (Exception $exception) {
                $this->Flash->error(__d('CakeDC/Users', 'Password could not be changed'));
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
     * @return void|\Cake\Network\Response
     */
    public function requestResetPassword()
    {
        $this->set('user', $this->getUsersTable()->newEntity());
        $this->set('_serialize', ['user']);
        if (!$this->request->is('post')) {
            return;
        }

        $reference = $this->request->getData('reference');
        try {
            $resetUser = $this->getUsersTable()->resetToken($reference, [
                'expiration' => Configure::read('Users.Token.expiration'),
                'checkActive' => false,
                'sendEmail' => true,
                'ensureActive' => Configure::read('Users.Registration.ensureActive')
            ]);
            if ($resetUser) {
                $msg = __d('CakeDC/Users', 'Please check your email to continue with password reset process');
                $this->Flash->success($msg);
            } else {
                $msg = __d('CakeDC/Users', 'The password token could not be generated. Please try again');
                $this->Flash->error($msg);
            }

            return $this->redirect(['action' => 'login']);
        } catch (UserNotFoundException $exception) {
            $this->Flash->error(__d('CakeDC/Users', 'User {0} was not found', $reference));
        } catch (UserNotActiveException $exception) {
            $this->Flash->error(__d('CakeDC/Users', 'The user is not active'));
        } catch (Exception $exception) {
            $this->Flash->error(__d('CakeDC/Users', 'Token could not be reset'));
        }
    }

    /**
     * resetGoogleAuthenticator
     *
     * Resets Google Authenticator token by setting secret_verified
     * to false.
     *
     * @param mixed $id of the user record.
     * @return mixed.
     */
    public function resetGoogleAuthenticator($id = null)
    {
        if ($this->request->is('post')) {
            try {
                $query = $this->getUsersTable()->query();
                $query->update()
                    ->set(['secret_verified' => false, 'secret' => null])
                    ->where(['id' => $id]);
                $query->execute();

                $message = __d('CakeDC/Users', 'Google Authenticator token was successfully reset');
                $this->Flash->success($message, 'default');
            } catch (\Exception $e) {
                $message = __d('CakeDC/Users', $e->getMessage());
                $this->Flash->error($message, 'default');
            }
        }

        return $this->redirect($this->request->referer());
    }
}
