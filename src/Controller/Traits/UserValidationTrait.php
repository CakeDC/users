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
use CakeDC\Users\Exception\TokenExpiredException;
use CakeDC\Users\Exception\UserAlreadyActiveException;
use CakeDC\Users\Exception\UserNotFoundException;
use Cake\Core\Configure;
use Cake\Http\Response;
use Exception;

/**
 * Covers the user validation
 *
 * @property \Cake\Http\ServerRequest $request
 */
trait UserValidationTrait
{
    /**
     * Validates email
     *
     * @param string $type 'email' or 'password' to validate the user
     * @param string $token token
     * @return Response
     */
    public function validate($type = null, $token = null)
    {
        try {
            switch ($type) {
                case 'email':
                    try {
                        $result = $this->getUsersTable()->validate($token, 'activateUser');
                        if ($result) {
                            $this->Flash->success(__d('CakeDC/Users', Configure::read('Messages.userValidation.accountValidated')));
                        } else {
                            $this->Flash->error(__d('CakeDC/Users', Configure::read('Messages.userValidation.failValidate')));
                        }
                    } catch (UserAlreadyActiveException $exception) {
                        $this->Flash->error(__d('CakeDC/Users', Configure::read('Messages.userValidation.alreadyActive')));
                    }
                    break;
                case 'password':
                    $result = $this->getUsersTable()->validate($token);
                    if (!empty($result)) {
                        $this->Flash->success(__d('CakeDC/Users', Configure::read('Messages.userValidation.tokenValidated')));
                        $this->request->getSession()->write(
                            Configure::read('Users.Key.Session.resetPasswordUserId'),
                            $result->id
                        );

                        return $this->redirect(['action' => 'changePassword']);
                    } else {
                        $this->Flash->error(__d('CakeDC/Users', Configure::read('Messages.userValidation.failTokenValidate')));
                    }
                    break;
                default:
                    $this->Flash->error(__d('CakeDC/Users', Configure::read('Messages.userValidation.invalidValidation')));
            }
        } catch (UserNotFoundException $ex) {
            $this->Flash->error(__d('CakeDC/Users', Configure::read('Messages.userValidation.tokenOrUserExist')));
        } catch (TokenExpiredException $ex) {
            $event = $this->dispatchEvent(UsersAuthComponent::EVENT_ON_EXPIRED_TOKEN, ['type' => $type]);
            if (!empty($event) && is_array($event->result)) {
                return $this->redirect($event->result);
            }
            $this->Flash->error(__d('CakeDC/Users', Configure::read('Messages.userValidation.expiredToken')));
        }

        return $this->redirect(['action' => 'login']);
    }

    /**
     * Resend Token validation
     *
     * @return mixed
     */
    public function resendTokenValidation()
    {
        $this->set('user', $this->getUsersTable()->newEntity());
        $this->set('_serialize', ['user']);
        if (!$this->request->is('post')) {
            return;
        }
        $reference = $this->request->getData('reference');
        try {
            if ($this->getUsersTable()->resetToken($reference, [
                'expiration' => Configure::read('Users.Token.expiration'),
                'checkActive' => true,
                'sendEmail' => true,
                'type' => 'email'
            ])) {
                $event = $this->dispatchEvent(UsersAuthComponent::EVENT_AFTER_RESEND_TOKEN_VALIDATION);
                if (!empty($event) && is_array($event->result)) {
                    return $this->redirect($event->result);
                }
                $this->Flash->success(__d(
                    'CakeDC/Users',
                    Configure::read('Messages.userValidation.tokenReset')
                ));
            } else {
                $this->Flash->error(__d('CakeDC/Users', Configure::read('Messages.userValidation.failTokenReset')));
            }

            return $this->redirect(['action' => 'login']);
        } catch (UserNotFoundException $ex) {
            $this->Flash->error(__d('CakeDC/Users', Configure::read('Messages.userValidation.userNotFound'), $reference));
        } catch (UserAlreadyActiveException $ex) {
            $this->Flash->error(__d('CakeDC/Users', Configure::read('Messages.userValidation.userAlreadyActive'), $reference));
        } catch (Exception $ex) {
            $this->Flash->error(__d('CakeDC/Users', Configure::read('Messages.userValidation.failTokenReset')));
        }
    }
}
