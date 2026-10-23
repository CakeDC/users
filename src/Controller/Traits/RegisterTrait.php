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

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use CakeDC\Users\UsersPlugin;
use function collection;

/**
 * Covers registration features and email token validation
 *
 * @property \Cake\Http\ServerRequest $request
 */
trait RegisterTrait
{
    use PasswordManagementTrait;

    /**
     * Register a new user
     *
     * @return mixed
     * @throws \Cake\Http\Exception\NotFoundException
     */
    public function register(): mixed
    {
        if (!Configure::read('Users.Registration.active')) {
            throw new NotFoundException();
        }

        $identity = $this->getRequest()->getAttribute('identity');
        $identity ??= [];
        $userId = $identity['id'] ?? null;
        if (!empty($userId) && !Configure::read('Users.Registration.allowLoggedIn')) {
            $this->Flash->error(__d('cake_d_c/users', 'You must log out to register a new user account'));

            return $this->redirect(Configure::read('Users.Profile.route'));
        }

        $usersTable = $this->getUsersTable();
        $user = $usersTable->newEmptyEntity();
        $validateEmail = (bool)Configure::read('Users.Email.validate');
        $useTos = (bool)Configure::read('Users.Tos.required');
        $tokenExpiration = Configure::read('Users.Token.expiration');
        $options = [
            'token_expiration' => $tokenExpiration,
            'validate_email' => $validateEmail,
            'use_tos' => $useTos,
        ];
        $requestData = $this->getRequest()->getData();
        $event = $this->dispatchEvent(UsersPlugin::EVENT_BEFORE_REGISTER, [
            'usersTable' => $usersTable,
            'options' => $options,
            'userEntity' => $user,
        ]);

        $result = $event->getResult();
        if ($result instanceof EntityInterface) {
            $data = $result->toArray();
            $data['password'] = $requestData['password'] ?? null; //since password is a hidden property
            $userSaved = $usersTable->getBehavior('Register')->register($user, $data, $options);
            $errors = collection($user->getErrors())->unfold()->toArray();
            if ($userSaved) {
                return $this->afterRegister($userSaved);
            } elseif (Configure::read('Users.Registration.showVerboseError') && count($errors) > 0) {
                $this->set(compact('user'));
                foreach ($errors as $error) {
                    $this->Flash->error($error);
                }

                return null;
            } else {
                $this->set(['user' => $user]);
                $this->Flash->error(__d('cake_d_c/users', 'The user could not be saved'));

                return null;
            }
        }
        if ($event->isStopped()) {
            return $this->redirect($event->getResult());
        }

        $this->set(['user' => $user]);
        $this->viewBuilder()->setOption('serialize', ['user']);

        if (!$this->getRequest()->is('post')) {
            return null;
        }

        if (!$this->validateRegisterPost()) {
            $this->Flash->error(__d('cake_d_c/users', 'Invalid reCaptcha'));

            return null;
        }

        $userSaved = $usersTable->getBehavior('Register')->register($user, $requestData, $options);
        $errors = collection($user->getErrors())->unfold()->toArray();
        if (!$userSaved && Configure::read('Users.Registration.showVerboseError') && count($errors) > 0) {
            foreach ($errors as $error) {
                $this->Flash->error($error);
            }

            return null;
        } elseif (!$userSaved) {
            $this->Flash->error(__d('cake_d_c/users', 'The user could not be saved'));

            return null;
        }

        return $this->afterRegister($userSaved);
    }

    /**
     * Check the POST and validate it for registration, for now we check the reCaptcha
     *
     * @return bool
     */
    protected function validateRegisterPost(): bool
    {
        if (!Configure::read('Users.reCaptcha.registration')) {
            return true;
        }

        return $this->validateReCaptcha(
            $this->getRequest()->getData('g-recaptcha-response'),
            $this->getRequest()->clientIp(),
        );
    }

    /**
     * Prepare flash messages after registration, and dispatch afterRegister event
     *
     * @param \Cake\Datasource\EntityInterface $userSaved User entity saved
     * @return \Cake\Http\Response
     */
    protected function afterRegister(EntityInterface $userSaved): Response
    {
        $validateEmail = (bool)Configure::read('Users.Email.validate');
        $message = __d('cake_d_c/users', 'You have registered successfully, please log in');
        if ($validateEmail) {
            $message = __d('cake_d_c/users', 'Please validate your account before log in');
        }
        $event = $this->dispatchEvent(UsersPlugin::EVENT_AFTER_REGISTER, [
            'user' => $userSaved,
        ]);
        $result = $event->getResult();
        if ($result instanceof Response) {
            return $result;
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
    public function validateEmail(?string $token = null): void
    {
        $this->validate('email', $token);
    }
}
