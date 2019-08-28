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

use CakeDC\Users\Controller\Component\UsersAuthComponent;
use CakeDC\Users\Exception\UserNotActiveException;
use CakeDC\Users\Exception\UserNotFoundException;
use CakeDC\Users\Exception\WrongPasswordException;
use Cake\Core\Configure;
use Cake\Log\Log;
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
     * Can be used while logged in for own password, as a superuser on any user, or while not logged in for reset
     * reset password with session key (email token has already been validated)
     *
     * @param int|string|null $id user_id, null for logged in user id
     *
     * @return mixed
     */
    public function changePassword($id = null)
    {
        $user = $this->getUsersTable()->newEntity();
        if ($this->Auth->user('id')) {
            if ($id && $this->Auth->user('is_superuser') && Configure::read('Users.Superuser.allowedToChangePasswords')) {
                // superuser editing any account's password
                $user->id = $id;
                $validatePassword = false;
                $redirect = ['action' => 'index'];
            } elseif (!$id || $id === $this->Auth->user('id')) {
                // normal user editing own password
                $user->id = $this->Auth->user('id');
                $validatePassword = true;
                $redirect = Configure::read('Users.Profile.route');
            } else {
                $this->Flash->error(__d('CakeDC/Users', 'Changing another user\'s password is not allowed'));
                $this->redirect(Configure::read('Users.Profile.route'));

                return;
            }
        } else {
            // password reset
            $user->id = $this->request->getSession()->read(Configure::read('Users.Key.Session.resetPasswordUserId'));
            $validatePassword = false;
            $redirect = $this->Auth->getConfig('loginAction');
            if (!$user->id) {
                $this->Flash->error(__d('CakeDC/Users', 'User was not found'));
                $this->redirect($this->Auth->getConfig('loginAction'));

                return;
            }
        }
        $this->set('validatePassword', $validatePassword);
        if ($this->request->is(['post', 'put'])) {
            try {
                $validator = $this->getUsersTable()->validationPasswordConfirm(new Validator());
                if ($validatePassword) {
                    $validator = $this->getUsersTable()->validationCurrentPassword($validator);
                }
                $user = $this->getUsersTable()->patchEntity(
                    $user,
                    $this->request->getData(),
                    [
                        'validate' => $validator,
                        'accessibleFields' => [
                            'current_password' => true,
                            'password' => true,
                            'password_confirm' => true,
                        ]
                    ]
                );
                if ($user->getErrors()) {
                    $this->Flash->error(__d('CakeDC/Users', 'Password could not be changed'));
                } else {
                    $result = $this->getUsersTable()->changePassword($user);
                    if ($result) {
                        $event = $this->dispatchEvent(UsersAuthComponent::EVENT_AFTER_CHANGE_PASSWORD, ['user' => $result]);
                        if (!empty($event) && is_array($event->result)) {
                            return $this->redirect($event->result);
                        }
                        $this->Flash->success(__d('CakeDC/Users', 'Password has been changed successfully'));

                        return $this->redirect($redirect);
                    } else {
                        $this->Flash->error(__d('CakeDC/Users', 'Password could not be changed'));
                    }
                }
            } catch (UserNotFoundException $exception) {
                $this->Flash->error(__d('CakeDC/Users', 'User was not found'));
            } catch (WrongPasswordException $wpe) {
                $this->Flash->error($wpe->getMessage());
            } catch (Exception $exception) {
                $this->Flash->error(__d('CakeDC/Users', 'Password could not be changed'));
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
                'ensureActive' => Configure::read('Users.Registration.ensureActive'),
                'type' => 'password'
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
            $this->log($exception->getMessage());
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
                if (empty($id)) {
                    throw new \Exception(__('Invalid user id'));
                }
                $query = $this->getUsersTable()->query();
                $query->update()
                    ->set(['secret_verified' => false, 'secret' => null])
                    ->where(['id' => $id]);
                $query->execute();

                $message = __d('CakeDC/Users', 'Google Authenticator token was successfully reset');
                $this->Flash->success($message, 'default');
            } catch (\Exception $e) {
                $this->Flash->error(__('Could not reset the token'), 'default');
            }
        }

        return $this->redirect($this->request->referer());
    }
}
