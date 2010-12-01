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
 * Testing Controller
 *
 * @package users
 * @subpackage users.tests.cases.controllers.components
 */
class UsersAuthTestController extends Controller {

/**
 * Components
 *
 * @var array
 */
	public $components = array(
		'Cookie',
		'Session',
		'Users.UsersAuth',
	);

/**
 * Empty login function
 *
 * @return void
 */
	public function login() {
		
	}

/**
 * Prevent actual redirection
 *
 * @param mixed $url URL To redirect to
 * @param string $status Status code to use
 * @param string $exit Exit after redirect
 * @return string URL to redirect to
 */
	public function redirect($url, $status = null, $exit = true) {
		return Router::url($url);
	}
}

/**
 * UsersAuth Component Tests
 *
 * @package users
 * @subpackage users.tests.cases.controllers.components
 */
class UsersAuthTestCase extends CakeTestCase {

/**
 * Setup for testing
 *
 * @return void
 */
	public function setUp() {
		$this->User = ClassRegistry::init('Users.User');
		$this->Users = new UsersAuthTestController();
		$this->Users->Component->init($this->Users);
		$this->Users->Component->initialize($this->Users);
		$this->Users->beforeFilter();
		ClassRegistry::addObject('view', new View($this->Users));
		$this->Users->Session->delete('Auth');
		$this->Users->Session->delete('Message.auth');

		$this->Users->params = Router::parse('users_auth_test/login');
		$this->Users->params['url']['url'] = 'users_auth_test/login';
		
		$this->Users->Auth->startup($this->Users);

		Router::reload();
	}

/**
 * Tear Down
 *
 * @return void
 */
	public function tearDown() {
		$this->Users->Session->delete('Auth');
		$this->Users->Session->delete('Message.auth');
		ClassRegistry::flush();
	}

/**
 * Test setting the cookie
 *
 * @return void
 */
	public function testSetCookie() {
		$this->Users->data = array(
			'User' => array(
				'remember_me' => 1,
				'username' => 'test',
				'password' => 'testtest'
			)
		);
		$this->Users->Auth->login();
//		$this->Users->Cookie->name = 'userTestCookie';
		$result = $this->Users->Cookie->read('User');
		$this->assertEqual($result, array(
			'username' => 'test',
			'password' => 'testtest'));
	}
}
