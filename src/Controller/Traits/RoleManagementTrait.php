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
trait RoleManagementTrait
{

    /**
     * Change Role
     * Can be used by superadmin to change user roles
     *
     * @param int|string|null $id user_id, null for logged in user id
     * @return mixed
     */
    public function changeRole($id = null)
    {
        $user = $this->getUsersTable()->newEntity([], ['validate' => false]);
        $user->setNew(false);

        $identity = $this->getRequest()->getAttribute('identity');
        $identity = $identity ?? [];
        $userId = $identity['id'] ?? null;

        if ($userId) {
            if ($id && $identity['is_superuser'] && Configure::read('Users.Superuser.allowedToChangeRoles')) {
                // superuser update user roles
                $user->id = $id;
                $redirect = ['action' => 'index'];
            }  else {
                $this->Flash->error(
                    __d('cake_d_c/users', 'Changing another user\'s role is not allowed')
                );
                $this->redirect(Configure::read('Users.Profile.route'));

                return;
            }
        } else {
            $this->Flash->error(
                __d('cake_d_c/users', 'Login to perform this action')
            );
            $this->redirect(Configure::read('Users.Profile.route'));
        }

        if ($this->getRequest()->is(['post', 'put'])) {
            try {
                $user = $this->getUsersTable()->patchEntity(
                    $user,
                    $this->getRequest()->getData(),
                    [
                        'accessibleFields' => [
                            'role' => true,
                        ],
                    ]
                );

                if ($user->getErrors()) {
                    $this->Flash->error(__d('cake_d_c/users', 'Role could not be changed'));
                } else {
                    $result = $this->getUsersTable()->save($user);
                    if ($result) {
                        $event = $this->dispatchEvent(Plugin::EVENT_AFTER_CHANGE_ROLE, ['user' => $result]);
                        if (!empty($event) && is_array($event->getResult())) {
                            return $this->redirect($event->getResult());
                        }
                        $this->Flash->success(__d('cake_d_c/users', 'Role has been changed successfully'));

                        return $this->redirect($redirect);
                    } else {
                        $this->Flash->error(__d('cake_d_c/users', 'Role could not be changed'));
                    }
                }
            } catch (UserNotFoundException $exception) {
                $this->Flash->error(__d('cake_d_c/users', 'User was not found'));
            } catch (Exception $exception) {
                $this->Flash->error(__d('cake_d_c/users', 'Role could not be changed'));
                $this->log($exception->getMessage());
            }
        }
        $this->set(compact('user'));
        $this->set('_serialize', ['user']);
    }
}
