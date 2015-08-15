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

namespace Users\Controller\Traits;

use Cake\Core\Configure;
use Cake\Network\Response;
use Exception;
use InvalidArgumentException;
use Users\Exception\TokenExpiredException;
use Users\Exception\UserAlreadyActiveException;
use Users\Exception\UserNotFoundException;

/**
 * Covers the user validation
 *
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
                            $this->Flash->success(__d('Users', 'User account validated successfully'));
                        } else {
                            $this->Flash->error(__d('Users', 'User account could not be validated'));
                        }
                    } catch (UserAlreadyActiveException $exception) {
                        $this->Flash->error(__d('Users', 'User already active'));
                    }
                    break;
                case 'password':
                    $result = $this->getUsersTable()->validate($token);
                    if (!empty($result)) {
                        $this->Flash->success(__d('Users', 'Reset password token was validated successfully'));
                        $this->request->session()->write(Configure::read('Users.Key.Session.resetPasswordUserId'), $result->id);
                        $this->redirect(['action' => 'changePassword']);
                    } else {
                        $this->Flash->error(__d('Users', 'Reset password token could not be validated'));
                    }
                    break;
                default:
                    throw new InvalidArgumentException();
            }
        } catch (UserNotFoundException $exception) {
            $this->Flash->error(__d('Users', 'Invalid token and/or email'));
        } catch (TokenExpiredException $exception) {
            $this->Flash->error(__d('Users', 'Token already expired'));
        } catch (InvalidArgumentException $iae) {
            $this->Flash->error(__d('Users', 'Invalid validation type'));
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
        $reference = $this->request->data('reference');
        try {
            if ($this->getUsersTable()->resetToken($reference, [
                'expiration' => Configure::read('Users.Token.expiration'),
                'checkActive' => true,
            ])) {
                $this->Flash->success(__d('Users', 'Token has been reset successfully'));
            } else {
                $this->Flash->error(__d('Users', 'Token could not be reset'));
            }
            return $this->redirect(['action' => 'login']);
        } catch (UserNotFoundException $exception) {
            $this->Flash->error(__d('Users', 'User {0} was not found', $reference));
        } catch (UserAlreadyActiveException $exception) {
            $this->Flash->error(__d('Users', 'User {0} is already active', $reference));
        } catch (Exception $exception) {
            $this->Flash->error(__d('Users', 'Token could not be reset'));
        }
    }
}
