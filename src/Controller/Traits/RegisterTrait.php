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

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Network\Exception\NotFoundException;
use Cake\Network\Response;
use InvalidArgumentException;
use CakeDC\Users\Controller\Component\UsersAuthComponent;

/**
 * Covers registration features and email token validation
 *
 */
trait RegisterTrait
{
    use PasswordManagementTrait;
    use ReCaptchaTrait;

    /**
     * Register a new user
     *
     * @return type
     */
    public function register()
    {
        if (!Configure::read('Users.Registration.active')) {
            throw new NotFoundException();
        }
        $usersTable = $this->getUsersTable();
        $user = $usersTable->newEntity();
        $validateEmail = (bool)Configure::read('Users.Email.validate');
        $useTos = (bool)Configure::read('Users.Tos.required');
        $tokenExpiration = Configure::read('Users.Token.expiration');
        $options = [
            'token_expiration' => $tokenExpiration,
            'validate_email' => $validateEmail,
            'use_tos' => $useTos
        ];
        $requestData = $this->request->data;
        $event = $this->dispatchEvent(UsersAuthComponent::EVENT_BEFORE_REGISTER, [
            'usersTable' => $usersTable,
            'options' => $options,
        ]);

        if ($event->result instanceof EntityInterface) {
            if ($userSaved = $usersTable->register($user, $event->result->toArray(), $options)) {
                return $this->_afterRegister($userSaved);
            }
        }
        if ($event->isStopped()) {
            return $this->redirect($event->result);
        }

        $this->set(compact('user'));
        $this->set('_serialize', ['user']);

        if (!$this->request->is('post')) {
            return;
        }

        $validPost = $this->_validateRegisterPost();
        if (!$validPost) {
            $this->Flash->error(__d('Users', 'The reCaptcha could not be validated'));
            return;
        }
        try {
            $userSaved = $usersTable->register($user, $requestData, $options);
        } catch (InvalidArgumentException $ex) {
            $this->Flash->error($ex->getMessage());
            return;
        }
        if (!$userSaved) {
            $this->Flash->error(__d('Users', 'The user could not be saved'));
            return;
        }

        return $this->_afterRegister($userSaved);
    }

    /**
     * Check the POST and validate it for registration, for now we check the reCaptcha
     *
     * @return bool
     */
    protected function _validateRegisterPost()
    {
        if (!Configure::read('Users.Registration.reCaptcha')) {
            return true;
        }
        $validReCaptcha = $this->validateReCaptcha(
            $this->request->data('g-recaptcha-response'),
            $this->request->clientIp()
        );
        return $validReCaptcha;
    }

    /**
     * Prepare flash messages after registration, and dispatch afterRegister event
     *
     * @param EntityInterface $userSaved User entity saved
     * @return Response
     */
    protected function _afterRegister(EntityInterface $userSaved)
    {
        $validateEmail = (bool)Configure::read('Users.Email.validate');
        $message = __d('Users', 'You have registered successfully, please log in');
        if ($validateEmail) {
            $message = __d('Users', 'Please validate your account before log in');
        }
        $event = $this->dispatchEvent(UsersAuthComponent::EVENT_AFTER_REGISTER, [
            'user' => $userSaved
        ]);
        if ($event->result instanceof Response) {
            return $event->result;
        }
        $this->Flash->success($message);
        return $this->redirect(['action' => 'login']);
    }

    /**
     * Validate an email
     *
     * @param string $token token
     * @return void
     */
    public function validateEmail($token = null)
    {
        $this->validate('email', $token);
    }
}
