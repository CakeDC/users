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

	public $name = 'UsersAuthTest';

	public $uses = array('Users.User');

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
		$this->Auth->login($this->data);
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

	public function test_action() {
		
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
 * 
 */
	public $plugin = 'users';

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
 * Setup for testing
 *
 * @return void
 */
	public function startTest() {
		$this->User = ClassRegistry::init('Users.User');
		$this->Users = new UsersAuthTestController();
		$this->Users->modelClass = 'User';
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
	public function endTest() {
		$this->Users->Cookie->destroy();
		$this->Users->Session->destroy();
		ClassRegistry::flush();
	}

/**
 * Test setting and destroying the user cookie
 *
 * @return void
 */
	public function testInitialize() {
		$this->User = ClassRegistry::init('Users.User');
		$this->Users = new UsersAuthTestController();
		$this->Users->modelClass = 'User';
		$this->Users->params = Router::parse('users_auth_test/login');
		$this->Users->params['url']['url'] = 'users_auth_test/login';
		$this->Users->Component->init($this->Users);
		$this->Users->Component->initialize($this->Users);

		$this->assertEqual($this->Users->Auth->cookieOptions, array(
			'domain' => 'plugins.cdc',
			'name' => 'Users',
			'keyname' => 'rememberMe',
			'time' => '1 Month',
			'path' => '/'));

	}

/**
 * Test setting and destroying the user cookie
 *
 * @return void
 */
	public function testSetCookie() {
		$this->Users->data = array(
			'User' => array(
				'remember_me' => 1,
				'email' => 'larry.masters@cakedc.com',
				'passwd' => 'test', null, true));

		$this->Users->Auth->startup(&$this->Users);
		$this->Users->login();
		$result = $this->Users->Cookie->read('rememberMe');

		$this->assertEqual($result, array(
			'email' => 'larry.masters@cakedc.com',
			'passwd' => Security::hash('test', null, true)));

		$this->assertEqual($this->Users->Session->read('Message.flash.message'), 'phpnut, you have successfully logged in.');

		$this->Users->Auth->logout();
		$result = $this->Users->Cookie->read('rememberMe');
		$this->assertFalse($result);
	}

	public function testNotAllowedAction() {
		$this->Users->Auth->startup(&$this->Users);
		$this->Users->test_action();
		$this->assertEqual($this->Users->Session->read('Message.auth.message'), 'Sorry, but you need to login to access this location.');
	}

/**
 * Test an invalid login
 *
 * @return void
 */
	public function testInvalidLogin() {
		$this->Users->data = array(
			'User' => array(
				'email' => 'invalid-email',
				'passwd' => 'testtest', null, true));

		// overwrite the defaults to match the test controller
		$this->Users->Auth->loginAction['controller'] = 'users_auth_test';
		$this->Users->Auth->loginAction['plugin'] = null;

		$this->Users->Auth->startup(&$this->Users);
		$this->assertFalse($this->Users->Auth->login());
		$this->assertEqual($this->Users->Session->read('Message.auth.message'), 'Invalid e-mail / password combination. Please try again');
	}

}