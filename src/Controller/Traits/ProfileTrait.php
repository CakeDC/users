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
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * Covers the profile action
 *
 */
trait ProfileTrait
{

    /**
     * Profile action
     * @param mixed $id Profile id object.
     * @return mixed
     */
    public function profile($id = null)
    {
        $loggedUserId = $this->Auth->user('id');
        $isCurrentUser = false;
        if (!Configure::read('Users.Profile.viewOthers') || empty($id)) {
            $id = $loggedUserId;
        }
        try {
            $appContain = (array)Configure::read('Auth.authenticate.' . \Cake\Controller\Component\AuthComponent::ALL . '.contain');
            $socialContain = Configure::read('Users.Social.login') ? ['SocialAccounts']: [];
            $user = $this->getUsersTable()->get($id, [
                    'contain' => array_merge((array)$appContain, (array)$socialContain)
                ]);
            $this->set('avatarPlaceholder', Configure::read('Users.Avatar.placeholder'));
            if ($user->id === $loggedUserId) {
                $isCurrentUser = true;
            }
        } catch (RecordNotFoundException $ex) {
            $this->Flash->error(__d('Users', 'User was not found'));
            return $this->redirect($this->request->referer());
        } catch (InvalidPrimaryKeyException $ex) {
            $this->Flash->error(__d('Users', 'Not authorized, please login first'));
            return $this->redirect($this->request->referer());
        }
        $this->set(compact('user', 'isCurrentUser'));
        $this->set('_serialize', ['user', 'isCurrentUser']);
    }
}
