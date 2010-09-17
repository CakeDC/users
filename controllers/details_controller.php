<?php
/**
 * Copyright 2010, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Users Details Controller
 *
 * @package users
 * @subpackage users.controllers
 */
class DetailsController extends UsersAppController {

/**
 * Name
 *
 * @var string
 */
	public $name = 'Details';

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
		$details = $this->Detail->find('all', array(
			'contain' => array(),
			'conditions' => array(
				'Detail.user_id' => $this->Auth->user('id'),
				'Detail.field LIKE' => 'user.%'),
			'order' => 'Detail.position DESC'));
		$this->set('details', $details);
	}

/**
 * View
 *
 * @param string $id Detail ID
 * @return void
 */
	public function view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__d('users', 'Invalid Detail.', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->set('detail', $this->Detail->read(null, $id));
	}

/**
 * Add
 *
 * @return void
 */
	public function add() {
		if (!empty($this->data)) {
			$userId = $this->Auth->user('id');
			foreach($this->data as $group => $options) {
				foreach($options as $key => $value) {
					$field = $group . '.' . $key;
					$this->Detail->updateAll(
						array('Detail.value' => "'$value'"),
						array('Detail.user_id' => $userId, 'Detail.field' => $field));
				}
			}
			$this->Session->setFlash(__d('users', 'Saved', true));
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

		if (!empty($this->data)) {
			$this->Detail->saveSection($this->Auth->user('id'), $this->data, $section);
			$this->data['Detail'] = $this->Detail->getSection($this->Auth->user('id'), $section);
			$this->Session->setFlash(sprintf(__d('users', '%s details saved', true), ucfirst($section)));
		}

		if (empty($this->data)) {
			$this->data['Detail'] = $this->Detail->getSection($this->Auth->user('id'), $section);
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
			$this->Session->setFlash(__d('users', 'Invalid id for Detail', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Detail->delete($id)) {
			$this->Session->setFlash(__d('users', 'Detail deleted', true));
			$this->redirect(array('action'=>'index'));
		}
	}

/**
 * Admin Index
 *
 * @return void
 */
	public function admin_index() {
		$this->Detail->recursive = 0;
		$this->set('details', $this->paginate());
	}

/**
 * Admin View
 *
 * @param string $id Detail ID
 * @return void
 */
	public function admin_view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__d('users', 'Invalid Detail.', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->set('detail', $this->Detail->read(null, $id));
	}

/**
 * Admin Add
 *
 * @return void
 */
	public function admin_add() {
		if (!empty($this->data)) {
			$this->Detail->create();
			if ($this->Detail->save($this->data)) {
				$this->Session->setFlash(__d('users', 'The Detail has been saved', true));
				$this->redirect(array('action'=>'index'));
			} else {
				$this->Session->setFlash(__d('users', 'The Detail could not be saved. Please, try again.', true));
			}
		}
		$groups = $this->Detail->Group->find('list');
		$users = $this->Detail->User->find('list');
		$this->set(compact('groups', 'users'));
	}

/**
 * Admin edit
 *
 * @param string $id Detail ID
 * @return void
 */
	public function admin_edit($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__d('users', 'Invalid Detail', true));
			$this->redirect(array('action'=>'index'));
		}
		if (!empty($this->data)) {
			if ($this->Detail->save($this->data)) {
				$this->Session->setFlash(__d('users', 'The Detail has been saved', true));
				$this->redirect(array('action'=>'index'));
			} else {
				$this->Session->setFlash(__d('users', 'The Detail could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Detail->read(null, $id);
		}
		$groups = $this->Detail->Group->find('list');
		$users = $this->Detail->User->find('list');
		$this->set(compact('groups','users'));
	}

/**
 * Admin Delete
 *
 * @param string $id Detail ID
 * @return void
 */
	public function admin_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__d('users', 'Invalid id for Detail', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Detail->delete($id)) {
			$this->Session->setFlash(__d('users', 'Detail deleted', true));
			$this->redirect(array('action'=>'index'));
		}
	}
}
