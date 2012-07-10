<?php
/**
 * Copyright 2010 - 2011, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2011, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('UsersAppController', 'Users.Controller');

/**
 * Users Details Controller
 *
 * @package users
 * @subpackage users.controllers
 */
class UserDetailsController extends UsersAppController {

/**
 * Name
 *
 * @var string
 */
	public $name = 'UserDetails';

/**
 * Helpers
 *
 * @var array
 */
	public $helpers = array('Html', 'Form');

/**
 * Index
 *
 * @return void
 */
	public function index() {
		$user_details = $this->UserDetail->find('all', array(
			'contain' => array(),
			'conditions' => array(
				'UserDetail.user_id' => $this->Auth->user('id'),
				'UserDetail.field LIKE' => 'user.%'),
			'order' => 'UserDetail.position DESC'));
		$this->set('user_details', $user_details);
	}

/**
 * View
 *
 * @param string $id Detail ID
 * @return void
 */
	public function view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__d('users', 'Invalid Detail.'));
			$this->redirect(array('action' => 'index'));
		}
		$this->set('user_detail', $this->UserDetail->read(null, $id));
	}

/**
 * Add
 *
 * @return void
 */
	public function add() {
		if (!empty($this->request->data)) {
			$userId = $this->Auth->user('id');
			foreach($this->request->data as $group => $options) {
				foreach($options as $key => $value) {
					$field = $group . '.' . $key;
					$this->UserDetail->updateAll(
						array('Detail.value' => "'$value'"),
						array('Detail.user_id' => $userId, 'Detail.field' => $field));
				}
			}
			$this->Session->setFlash(__d('users', 'Saved'));
		}
		$this->redirect(array('action' => 'index'));
	}

/**
 * Edit
 *
 * Allows a logged in user to edit his own profile settings
 *
 * @param string $section Section name
 * @return void
 */
	public function edit($section = 'user') {
		if (!isset($section)) {
			$section = 'user';
		}

		if (!empty($this->request->data)) {
			$this->UserDetail->saveSection($this->Auth->user('id'), $this->request->data, $section);
			$this->Session->setFlash(sprintf(__d('users', '%s details saved'), ucfirst($section)));
		}

		if (empty($this->request->data)) {
            $detail = $this->UserDetail->getSection($this->Auth->user('id'), $section);
            $this->request->data['UserDetail'] = $detail[$section];
		}

		$this->set('section', $section);
	}

/**
 * Delete
 *
 * @param string $id Detail ID
 * @return void
 */
	public function delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__d('users', 'Invalid id for Detail'));
			$this->redirect(array('action' => 'index'));
		}
		if ($this->UserDetail->delete($id)) {
			$this->Session->setFlash(__d('users', 'User Detail deleted'));
			$this->redirect(array('action' => 'index'));
		}
	}

/**
 * Admin Index
 *
 * @return void
 */
	public function admin_index() {
		$this->UserDetail->recursive = 0;
		$this->set('user_details', $this->paginate());
	}

/**
 * Admin View
 *
 * @param string $id Detail ID
 * @return void
 */
	public function admin_view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__d('users', 'Invalid Detail.'));
			$this->redirect(array('action' => 'index'));
		}
		$this->set('user_detail', $this->UserDetail->read(null, $id));
	}

/**
 * Admin Add
 *
 * @return void
 */
	public function admin_add() {
		if (!empty($this->request->data)) {
			$this->UserDetail->create();
			if ($this->UserDetail->save($this->request->data)) {
				$this->Session->setFlash(__d('users', 'The Detail has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__d('users', 'The Detail could not be saved. Please, try again.'));
			}
		}

		$users = $this->UserDetail->User->find('list');
		$this->set(compact('users'));
	}

/**
 * Admin edit
 *
 * @param string $id Detail ID
 * @return void
 */
	public function admin_edit($id = null) {
		if (!$id && empty($this->request->data)) {
			$this->Session->setFlash(__d('users', 'Invalid Detail'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->request->data)) {
			if ($this->UserDetail->save($this->request->data)) {
				$this->Session->setFlash(__d('users', 'The Detail has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__d('users', 'The Detail could not be saved. Please, try again.'));
			}
		}
		if (empty($this->request->data)) {
			$this->request->data = $this->UserDetail->read(null, $id);
		}

		$users = $this->UserDetail->User->find('list');
		$this->set(compact('users'));
	}

/**
 * Admin Delete
 *
 * @param string $id Detail ID
 * @return void
 */
	public function admin_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__d('users', 'Invalid id for Detail'));
			$this->redirect(array('action' => 'index'));
		}
		if ($this->UserDetail->delete($id)) {
			$this->Session->setFlash(__d('users', 'User Detail deleted'));
			$this->redirect(array('action' => 'index'));
		}
	}
}
