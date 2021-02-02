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
use CakeDC\Users\Exception\TokenExpiredException;
use CakeDC\Users\Exception\UserAlreadyActiveException;
use CakeDC\Users\Exception\UserNotFoundException;
use CakeDC\Users\Plugin;
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
     * @return \Cake\Http\Response
     */
    public function validate($type = null, $token = null)
    {
        try {
            switch ($type) {
                case 'email':
                    try {
                        $result = $this->getUsersTable()->validate($token, 'activateUser');
                        if ($result) {
                            $event = $this->dispatchEvent(Plugin::EVENT_AFTER_EMAIL_TOKEN_VALIDATION, ['user' => $result]);
                            if (!empty($event) && is_array($event->getResult())) {
                                return $this->redirect($event->getResult());
                            }
                            $this->Flash->success(__d('cake_d_c/users', 'User account validated successfully'));
                        } else {
                            $this->Flash->error(__d('cake_d_c/users', 'User account could not be validated'));
                        }
                    } catch (UserAlreadyActiveException $exception) {
                        $this->Flash->error(__d('cake_d_c/users', 'User already active'));
                    }
                    break;
                case 'password':
                    $result = $this->getUsersTable()->validate($token);
                    if (!empty($result)) {
                        $this->Flash->success(__d('cake_d_c/users', 'Reset password token was validated successfully'));
                        $this->getRequest()->getSession()->write(
                            Configure::read('Users.Key.Session.resetPasswordUserId'),
                            $result->id
                        );

                        return $this->redirect(['action' => 'changePassword']);
                    } else {
                        $this->Flash->error(__d('cake_d_c/users', 'Reset password token could not be validated'));
                    }
                    break;
                default:
                    $this->Flash->error(__d('cake_d_c/users', 'Invalid validation type'));
            }
        } catch (UserNotFoundException $ex) {
            $this->Flash->error(__d('cake_d_c/users', 'Invalid token or user account already validated'));
        } catch (TokenExpiredException $ex) {
            $event = $this->dispatchEvent(Plugin::EVENT_ON_EXPIRED_TOKEN, ['type' => $type]);
            if (!empty($event) && is_array($event->getResult())) {
                return $this->redirect($event->getResult());
            }
            $this->Flash->error(__d('cake_d_c/users', 'Token already expired'));
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
        $this->set('user', $this->getUsersTable()->newEntity([], ['validate' => false]));
        $this->set('_serialize', ['user']);
        if (!$this->getRequest()->is('post')) {
            return;
        }
        $reference = $this->getRequest()->getData('reference');
        try {
            if (
                $this->getUsersTable()->resetToken($reference, [
                'expiration' => Configure::read('Users.Token.expiration'),
                'checkActive' => true,
                'sendEmail' => true,
                'type' => 'email',
                ])
            ) {
                $event = $this->dispatchEvent(Plugin::EVENT_AFTER_RESEND_TOKEN_VALIDATION);
                $result = $event->getResult();
                if (!empty($event) && is_array($result)) {
                    return $this->redirect($result);
                }
                $this->Flash->success(__d(
                    'cake_d_c/users',
                    'Token has been reset successfully. Please check your email.'
                ));
            } else {
                $this->Flash->error(__d('cake_d_c/users', 'Token could not be reset'));
            }

            return $this->redirect(['action' => 'login']);
        } catch (UserNotFoundException $ex) {
            $this->Flash->error(__d('cake_d_c/users', 'User {0} was not found', $reference));
        } catch (UserAlreadyActiveException $ex) {
            $this->Flash->error(__d('cake_d_c/users', 'User {0} is already active', $reference));
        } catch (Exception $ex) {
            $this->Flash->error(__d('cake_d_c/users', 'Token could not be reset'));
        }
    }
}
