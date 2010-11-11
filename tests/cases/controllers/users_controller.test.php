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

App::import('Controller', 'Users.Users');
App::import('Model', 'Users.User');
App::import('Component', array('Auth', 'Cookie', 'Session'));

/**
 * TestUsersController
 *
 * @package users
 * @subpackage users.tests.controllers
 */
class TestUsersController extends UsersController {

/**
 * Name
 *
 * @var string
 */
	public $name = 'Users';

/**
 * 
 */
	public $uses = array('Users.User');

/**
 * Public interface to _setCookie
 */
	public function setCookie($options = array()) {
		parent::_setCookie($options);
	}

/**
 * Auto render
 *
 * @var boolean
 */
	public $autoRender = false;

/**
 * Redirect URL
 *
 * @var mixed
 */
	public $redirectUrl = null;

/**
 * Override controller method for testing
 */
	public function redirect($url, $status = null, $exit = true) {
		$this->redirectUrl = $url;
	}

/**
 * Override controller method for testing
 */
	public function render($action = null, $layout = null, $file = null) {
		$this->renderedView = $action;
	}
}

class UsersControllerTestCase extends CakeTestCase {

/**
 * Instance of the controller
 *
 * @var mixed
 */
	public $Users = null;

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.users.user',
		'plugin.users.detail',
		'plugin.users.identity');

/**
 * Sampletdata used for post data
 *
 * @var array
 */
	public $usersData = array(
		'admin' => array('email' => 'larry.masters@cakedc.com', 'username' => 'phpnut', 'passwd' => 'test'),
		'validUser' => array('email' => 'florian.kraemer@cakedc.com', 'username' => 'floriank', 'passwd' => 'secretkey', 'redirect' => '/user/burzum'),
		'invalidUser' => array('email' => 'wronguser@wronguser.com', 'username' => 'invalidUser', 'passwd' => 'invalid-password!'));

/**
 * Start test
 *
 * @return void
 */
	public function startTest() {
		Configure::write('App.UserClass', null);
		$this->Users = new TestUsersController();
		$this->Users->constructClasses();
		$this->Users->Component->init($this->Users);
		$this->Users->Component->initialize($this->Users);
		$this->Users->params = array(
			'pass' => array(),
			'named' => array(),
			'controller' => 'users',
			'admin' => false,
			'plugin' => 'users',
			'url' => array());
		$this->Users->Email->delivery = 'debug';
	}

/**
 * Test controller instance
 *
 * @return void
 */
	public function testUsersControllerInstance() {
		$this->assertTrue(is_a($this->Users, 'UsersController'));
	}

/**
 * Test the user login
 *
 * @return void
 */
	public function testUserLogin() {
		$this->Users->params['action'] = 'login';
		$this->Users->Component->startup($this->Users);

		$this->Users->User->save(array(
			'User' => array(
				'id'  => '1',
				'username' => 'testuser',
				'slug' => 'testuser',
				'passwd'  => Security::hash('test', null, true),
			)), false);

		$this->__setPost(array('User' => $this->usersData['admin']));
 		$this->Users->beforeFilter();
		$this->Users->params = array(
			'controller' => 'users',
			'action' => 'login',
			'admin' => false,
			'plugin' => 'users',
			'url' => array(
				'url' => '/users/users/login'));

		$this->Users->Component->startup($this->Users);
		$this->Users->login();
		$this->assertEqual($this->Users->Session->read('Message.flash.message'), __d('users', 'testuser, you have successfully logged in.', true));
		$this->assertEqual(Router::normalize($this->Users->redirectUrl), Router::normalize(Router::url($this->Users->Auth->loginRedirect)));

		$this->__setPost(array('User' => $this->usersData['invalidUser']));
		$this->Users->beforeFilter();
		$this->Users->login();
		$this->assertEqual($this->Users->Session->read('Message.auth.message'), __d('users', 'Invalid e-mail / password combination. Please try again', true));
	}

/**
 * Test user registration
 *
 */
	public function testAdd() {
		$this->Users->params['action'] = 'add';

		$this->__setPost(array(
			'User' => array(
				'username' => 'newUser',
				'email' => 'newUser@newemail.com',
				'passwd' => 'password',
				'temppassword' => 'password',
				'tos' => 1)));
		$this->Users->beforeFilter();
		$this->Users->add();
		$this->assertEqual($this->Users->Session->read('Message.flash.message'), __d('users', 'Your account has been created. You should receive an e-mail shortly to authenticate your account. Once validated you will be able to login.', true));

		$this->__setPost(array(
			'User' => array(
				'username' => 'newUser',
				'email' => '',
				'passwd' => '',
				'temppassword' => '',
				'tos' => 0)));
		$this->Users->beforeFilter();
		$this->Users->add();
		$this->assertEqual($this->Users->Session->read('Message.flash.message'), __d('users', 'Your account could not be created. Please, try again.', true));
	}

/**
 * Test
 *
 */
	public function testVerify() {
		$this->Users->beforeFilter();
		$this->Users->User->id = '37ea303a-3bdc-4251-b315-1316c0b300fa';
		$this->Users->User->saveField('email_token_expires', date('Y-m-d H:i:s', strtotime('+1 year')));
		$this->Users->verify('email', 'testtoken2');
		$this->assertEqual($this->Users->Session->read('Message.flash.message'), __d('users', 'Your e-mail has been validated. You may now login.', true));

		$this->Users->beforeFilter();
		$this->Users->verify('email', 'invalid-token');
		$this->assertEqual($this->Users->Session->read('Message.flash.message'), __d('users', 'The url you have accessed is no longer valid', true));
	}

/**
 * Test logout
 *
 * @return void
 */
	public function testLogout() {
		$this->Users->beforeFilter();
		$this->Users->Session->write('Auth.User', $this->usersData['validUser']);
		$this->Users->logout();
		$this->assertEqual($this->Users->Session->read('Message.flash.message'), __d('users', 'floriank, you have successfully logged out.', true));
		$this->assertEqual($this->Users->redirectUrl, '/');
	}

/**
 * testIndex
 *
 * @return void
 */
	public function testIndex() {
		$this->Users->params = array(
			'url' => array());
		$this->Users->passedArgs = array();
 		$this->Users->index();
		$this->assertTrue(isset($this->Users->viewVars['users']));
	}

/**
 * testView
 *
 * @return void
 */
	public function testView() {
 		$this->Users->view('phpnut');
		$this->assertTrue(isset($this->Users->viewVars['user']));

		$this->Users->view('INVALID-SLUG');
		$this->assertEqual($this->Users->redirectUrl, '/');
	}

/**
 * testSearch
 *
 * @return void
 */
	public function testSearch() {
		$this->Users->params = array(
			'url' => array(),
			'named' => array(
				'search' => 'phpnut'));
		$this->Users->passedArgs = array();
 		$this->Users->search();
		$this->assertTrue(isset($this->Users->viewVars['users']));
	}

/**
 * change_password
 *
 * @return void
 */
	public function testChangePassword() {
		$this->Users->Session->write('Auth.User.id', '1');
		$this->Users->data = array(
			'User' => array(
				'new_password' => 'newpassword',
				'confirm_password' => 'newpassword',
				'old_password' => 'test'));
		$this->Users->change_password();
		$this->assertEqual($this->Users->redirectUrl, '/');
	}

/**
 * testEdit
 *
 * @return void
 */
	public function testEdit() {
		$this->Users->Session->write('Auth.User.id', '1');
		$this->Users->edit();
		$this->assertTrue(!empty($this->Users->data));
	}

/**
 * testResetPassword
 *
 * @return void
 */
	public function testResetPassword() {
		$this->Users->User->id = '1';
		$this->Users->User->saveField('email_token_expires', date('Y-m-d H:i:s', strtotime('+1 year')));
		$this->Users->data = array(
			'User' => array(
				'email' => 'larry.masters@cakedc.com'));
		$this->Users->reset_password();
		$this->assertEqual($this->Users->redirectUrl, array('action' => 'login'));


		$this->Users->data = array(
			'User' => array(
				'new_password' => 'newpassword',
				'confirm_password' => 'newpassword'));
		$this->Users->reset_password('testtoken');
		$this->assertEqual($this->Users->redirectUrl, $this->Users->Auth->loginAction);
	}

/**
 * testAdminIndex
 *
 * @return void
 */
	public function testAdminIndex() {
		$this->Users->params = array(
			'url' => array(),
			'named' => array(
				'search' => 'phpnut'));
		$this->Users->passedArgs = array();
 		$this->Users->admin_index();
		$this->assertTrue(isset($this->Users->viewVars['users']));
	}

/**
 * testAdminView
 *
 * @return void
 */
	public function testAdminView() {
 		$this->Users->admin_view('1');
		$this->assertTrue(isset($this->Users->viewVars['user']));
	}

/**
 * testAdminDelete
 *
 * @return void
 */
	public function testAdminDelete() {
		$this->Users->User->id = '1';
		$this->assertTrue($this->Users->User->exists(true));
		$this->Users->admin_delete('1');
		$this->assertEqual($this->Users->redirectUrl, array('action' => 'index'));
		$this->assertFalse($this->Users->User->exists(true));

		$this->Users->admin_delete('INVALID-ID');
		$this->assertEqual($this->Users->redirectUrl, array('action' => 'index'));
	}

/**
 * Test
 *
 */
	private function __setPost($data = array()) {
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$this->Users->data = array_merge($data, array('_method' => 'POST'));
	}

/**
 * Test
 *
 */
	private function __setGet() {
		$_SERVER['REQUEST_METHOD'] = 'GET';
	}

/**
 * Test
 *
 */
	public function endTest() {
		$this->Users->Session->destroy();
		unset($this->Users);
		ClassRegistry::flush();
	}

}
