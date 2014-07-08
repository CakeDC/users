<?php
/**
 * Copyright 2010 - 2014, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2014, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('UsersController', 'Users.Controller');
App::uses('User', 'Users.Model');
App::uses('AuthComponent', 'Controller/Component');
App::uses('CookieComponent', 'Controller/Component');
App::uses('SessionComponent', 'Controller/Component');
App::uses('RememberMeComponent', 'Users.Controller/Component');
App::uses('Security', 'Utility');
app::uses('CakeEmail', 'Network/Email');

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
 * Models
 *
 * @var array
 */
	public $uses = array('Users.User');

/**
 * beforeFilter Callback
 *
 * @return void
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->authorize = array('Controller');
		$this->Auth->fields = array('username' => 'email', 'password' => 'password');
		$this->Auth->loginAction = array('controller' => 'users', 'action' => 'login', 'plugin' => 'users');
		$this->Auth->loginRedirect = $this->Session->read('Auth.redirect');
		$this->Auth->logoutRedirect = '/';
		$this->Auth->authError = __d('users', 'Sorry, but you need to login to access this location.');
		$this->Auth->autoRedirect = true;
		$this->Auth->userModel = 'User';
		$this->Auth->userScope = array(
			'OR' => array(
				'AND' =>
					array('User.active' => 1, 'User.email_verified' => 1
				)
			)
		);
	}

/**
 * Public interface to _setCookie
 */
	public function setCookie($options = array()) {
		parent::_setCookie($options);
	}

/**
 * Public intefface to _getMailInstance
 */
	public function getMailInstance() {
		return parent::_getMailInstance();
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
 * CakeEmail Mock
 *
 * @var object
 */
	public $CakeEmail = null;

/**
 * Override controller method for testing
 */
	public function redirect($url, $status = null, $exit = true) {
		$this->redirectUrl = $url;
	}

/**
 * Override controller method for testing
 *
 * @param string
 * @param string
 * @param string
 * @return string
 */
	public function render($action = null, $layout = null, $file = null) {
		$this->renderedView = $action;
	}

/**
 * Overriding the original method to return a mock object
 *
 * @return object CakeEmail instance
 */
	protected function _getMailInstance() {
		return $this->CakeEmail;
	}

}

/**
 * Email configuration override for testing
 */
class EmailConfig {

	public $default = array(
		'transport' => 'Debug',
		'from' => 'default@example.com',
	);

	public $another = array(
		'transport' => 'Debug',
		'from' => 'another@example.com',
	);
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
	);

/**
 * Sampletdata used for post data
 *
 * @var array
 */
	public $usersData = array(
		'admin' => array(
			'email' => 'adminuser@cakedc.com',
			'username' => 'adminuser',
			'password' => 'test'),
		'validUser' => array(
			'email' => 'testuser@cakedc.com',
			'username' => 'testuser',
			'password' => 'secretkey',
			'redirect' => '/user/slugname'),
		'invalidUser' => array(
			'email' => 'wronguser@wronguser.com',
			'username' => 'invalidUser',
			'password' => 'invalid-password!'),
	);

/**
 * Start test
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		Configure::write('Config.language', 'eng');
		Configure::write('App.UserClass', null);

		$request = new CakeRequest();
		$response = $this->getMock('CakeResponse');

		$this->Users = new TestUsersController($request, $response);
		$this->Users->constructClasses();
		$this->Users->request->params = array(
			'pass' => array(),
			'named' => array(),
			'controller' => 'users',
			'admin' => false,
			'plugin' => 'users',
			'url' => array());

		if (CakePlugin::loaded('Search')) {
			$this->Users->Prg = $this->getMock('PrgComponent',
				array('commonProcess'),
				array($this->Users->Components, array()));
		}

		$this->Users->CakeEmail = $this->getMock('CakeEmail');
		$this->Users->CakeEmail->expects($this->any())
			->method('to')
			->will($this->returnSelf());
		$this->Users->CakeEmail->expects($this->any())
			->method('from')
			->will($this->returnSelf());
		$this->Users->CakeEmail->expects($this->any())
			->method('subject')
			->will($this->returnSelf());
		$this->Users->CakeEmail->expects($this->any())
			->method('template')
			->will($this->returnSelf());
		$this->Users->CakeEmail->expects($this->any())
			->method('viewVars')
			->will($this->returnSelf());
		$this->Users->CakeEmail->expects($this->any())
			->method('emailFormat')
			->will($this->returnSelf());

		$this->Users->Components->disable('Security');
	}

/**
 * Test controller instance
 *
 * @return void
 */
	public function testUsersControllerInstance() {
		$this->assertInstanceOf('UsersController', $this->Users);
	}

/**
 * Test the user login
 *
 * @return void
 */
	public function testUserLogin() {
		$this->Users->request->params['action'] = 'login';
		$this->__setPost(array('User' => $this->usersData['admin']));
		$this->Users->request->url = '/users/users/login';

		$this->Collection = $this->getMock('ComponentCollection');
		$session = $this->getMock('SessionComponent', array('setFlash'), array($this->Collection));
		$this->Users->Session = $session;
		$this->Users->Session->expects($this->any())
			->method('setFlash')
			->with(__d('users', 'adminuser you have successfully logged in'));
		$this->Users->Auth = $this->getMock('AuthComponent', array('login', 'user', 'redirectUrl'), array($this->Collection));
		$this->Users->Auth->Session = $session;
		$this->Users->Auth->expects($this->once())
			->method('login')
			->will($this->returnValue(true));
		$this->Users->Auth->staticExpects($this->at(0))
			->method('user')
			->with('last_login')
			->will($this->returnValue(1));
		$this->Users->Auth->staticExpects($this->at(1))
			->method('user')
			->with('id')
			->will($this->returnValue(1));
		$this->Users->Auth->staticExpects($this->at(2))
			->method('user')
			->with('username')
			->will($this->returnValue('adminuser'));

		$this->Users->Auth->expects($this->once())
			->method('redirectUrl')
			->with(null)
			->will($this->returnValue(Router::normalize('/')));

		$this->Users->RememberMe = $this->getMock('RememberMeComponent', array(), array($this->Collection));
		$this->Users->RememberMe->expects($this->any())
			->method('destroyCookie');

		$this->Users->login();
		$this->assertEquals(Router::normalize($this->Users->redirectUrl), Router::normalize(Router::url($this->Users->Auth->loginRedirect)));
	}

/**
 * We should not see any flash message if we GET the login action
 *
 * @return void
 */
	public function testUserLoginGet() {
		// test with the login action
		$this->Users->request->url = '/users/users/login';
		$this->Users->request->params['action'] = 'login';
		$this->__setGet();
		$this->Users->login();
		$this->Collection = $this->getMock('ComponentCollection');
		$this->Users->Session = $this->getMock('SessionComponent', array('setFlash'), array($this->Collection));
		$this->Users->Session->expects($this->never())
			->method('setFlash');
	}

/**
 * testFailedUserLogin
 *
 * @return void
 */
	public function testFailedUserLogin() {
		$this->Users->request->params['action'] = 'login';
		$this->__setPost(array('User' => $this->usersData['invalidUser']));
		$this->Collection = $this->getMock('ComponentCollection');
		$this->Users->Auth = $this->getMock('AuthComponent', array('flash', 'login'), array($this->Collection));
		$this->Users->Auth->expects($this->once())
			->method('login')
			->will($this->returnValue(false));
		$this->Users->Auth->expects($this->once())
			->method('flash')
			->with(__d('users', 'Invalid e-mail / password combination. Please try again'));
		$this->Users->login();
	}

/**
 * Test user registration
 *
 * @return void
 */
	public function testAdd() {
		$this->Users->CakeEmail->expects($this->at(0))
			->method('send');
		$_SERVER['HTTP_HOST'] = 'test.com';
		$this->Users->params['action'] = 'add';
		$this->__setPost(array(
			'User' => array(
				'username' => 'newUser',
				'email' => 'newUser@newemail.com',
				'password' => 'password',
				'temppassword' => 'password',
				'tos' => 1)));
		$this->Users->beforeFilter();
		$this->Collection = $this->getMock('ComponentCollection');
		$this->Users->Session = $this->getMock('SessionComponent', array('setFlash'), array($this->Collection));
		$this->Users->Session->expects($this->once())
			->method('setFlash')
			->with(__d('users', 'Your account has been created. You should receive an e-mail shortly to authenticate your account. Once validated you will be able to login.'));
		$this->Users->add();
		$this->__setPost(array(
			'User' => array(
				'username' => 'newUser',
				'email' => '',
				'password' => '',
				'temppassword' => '',
				'tos' => 0)));
		$this->Users->beforeFilter();
		$this->Users->Session = $this->getMock('SessionComponent', array('setFlash'), array($this->Collection));
		$this->Users->Session->expects($this->once())
			->method('setFlash')
			->with(__d('users', 'Your account could not be created. Please, try again.'));
		$this->Users->add();
	}

/**
 * Test
 *
 * @return void
 */
	public function testVerify() {
		$this->Users->beforeFilter();
		$this->Users->User->id = '37ea303a-3bdc-4251-b315-1316c0b300fa';
		$this->Users->User->saveField('email_token_expires', date('Y-m-d H:i:s', strtotime('+1 year')));
		$this->Collection = $this->getMock('ComponentCollection');
		$this->Users->Session = $this->getMock('SessionComponent', array('setFlash'), array($this->Collection));
		$this->Users->Session->expects($this->once())
			->method('setFlash')
			->with(__d('users', 'Your e-mail has been validated!'));

		$this->Users->verify('email', 'testtoken2');

		$this->Users->beforeFilter();
		$this->Users->Session = $this->getMock('SessionComponent', array('setFlash'), array($this->Collection));
		$this->Users->Session->expects($this->once())
			->method('setFlash')
			->with(__d('users', 'Invalid token, please check the email you were sent, and retry the verification link.'));

		$this->Users->verify('email', 'invalid-token');
	}

/**
 * Test logout
 *
 * @return void
 */
	public function testLogout() {
		$this->Users->beforeFilter();
		$this->Collection = $this->getMock('ComponentCollection');
		$this->Users->Cookie = $this->getMock('CookieComponent', array(), array($this->Collection));
		$this->Users->Session = $this->getMock('SessionComponent', array('setFlash', 'destroy'), array($this->Collection));
		$this->Users->Session->expects($this->once())
			->method('setFlash')
			->with(__d('users', 'testuser you have successfully logged out'));
		$this->Users->Session->expects($this->once())
			->method('destroy');
		$this->Users->Auth = $this->getMock('AuthComponent', array('logout', 'user'), array($this->Collection));
		$this->Users->Auth->expects($this->once())
			->method('logout')
			->will($this->returnValue('/'));
		$this->Users->Auth->staticExpects($this->at(0))
			->method('user')
			->will($this->returnValue($this->usersData['validUser']));
		$this->Users->RememberMe = $this->getMock('RememberMeComponent', array(), array($this->Collection));
		$this->Users->RememberMe->expects($this->any())
			->method('destroyCookie');

		$this->Users->logout();
		$this->assertEquals($this->Users->redirectUrl, '/');
	}

/**
 * testIndex
 *
 * @return void
 */
	public function testIndex() {
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
		$this->Users->view('adminuser');
		$this->assertTrue(isset($this->Users->viewVars['user']));

		$this->Users->view('INVALID-SLUG');
		$this->assertEquals($this->Users->redirectUrl, '/');
	}

/**
 * change_password
 *
 * @return void
 */
	public function testChangePassword() {
		$this->Collection = $this->getMock('ComponentCollection');
		$this->Users->Auth = $this->getMock('AuthComponent', array('user'), array($this->Collection));
		$this->Users->Auth->staticExpects($this->once())
			->method('user')
			->with('id')
			->will($this->returnValue(1));
		$this->__setPost(array(
			'User' => array(
				'new_password' => 'newpassword',
				'confirm_password' => 'newpassword',
				'old_password' => 'test')));
		$this->Users->RememberMe = $this->getMock('RememberMeComponent', array(), array($this->Collection));
		$this->Users->RememberMe->expects($this->any())
			->method('destroyCookie');

		$this->Users->change_password();
		$this->assertEquals($this->Users->redirectUrl, '/');
	}

/**
 * testResetPassword
 *
 * @return void
 */
	public function testResetPassword() {
		$this->Users->CakeEmail->expects($this->at(0))
			->method('send');
		$_SERVER['HTTP_HOST'] = 'test.com';
		$this->Users->User->id = '1';
		$this->Users->User->saveField('email_token_expires', date('Y-m-d H:i:s', strtotime('+1 year')));
		$this->Users->data = array(
			'User' => array(
				'email' => 'adminuser@cakedc.com'
			)
		);
		$this->Users->reset_password();
		$this->assertEquals($this->Users->redirectUrl, array('action' => 'login'));
		$this->Users->data = array(
			'User' => array(
				'new_password' => 'newpassword',
				'confirm_password' => 'newpassword'
			)
		);
		$this->Users->reset_password('testtoken');
		$this->assertEquals($this->Users->redirectUrl, array('action' => 'reset_password'));
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
				'search' => 'adminuser'));
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
		$this->assertEquals($this->Users->redirectUrl, array('action' => 'index'));
		$this->assertFalse($this->Users->User->exists(true));
		$this->Users->admin_delete('INVALID-ID');
		$this->assertEquals($this->Users->redirectUrl, array('action' => 'index'));
	}

/**
 * Test setting the cookie
 *
 * @return void
 */
	public function testSetCookie() {
		$this->__setPost(array(
			'User' => array(
				'remember_me' => 1,
				'email' => 'testuser@cakedc.com',
				'username' => 'test',
				'password' => 'testtest')));
		$this->Collection = $this->getMock('ComponentCollection');
		$this->Users->RememberMe = $this->getMock('RememberMeComponent', array(), array($this->Collection));
		$this->Users->RememberMe->expects($this->once())
			->method('configureCookie')
			->with(array('name' => 'userTestCookie'));
		$this->Users->RememberMe->expects($this->once())
			->method('setCookie');
		$this->Users->setCookie(array(
			'name' => 'userTestCookie'));
		$this->assertEquals($this->Users->RememberMe->settings['cookieKey'], 'rememberMe');
	}

/**
 * Test getting default and setted email instance config
 *
 * @return void
 */
	public function testGetMailInstance() {
		$defaultConfig = $this->Users->getMailInstance()->config();
		$this->assertEquals($defaultConfig['from'], 'default@example.com');
		Configure::write('Users.emailConfig', 'another');
		$anotherConfig = $this->Users->getMailInstance()->config();
		$this->assertEquals($anotherConfig['from'], 'another@example.com');
		$this->setExpectedException('ConfigureException');
		Configure::write('Users.emailConfig', 'doesnotexist');
		$anotherConfig = $this->Users->getMailInstance()->config();
	}

/**
 * Test
 *
 * @var array $data
 * @return void
 */
	private function __setPost($data = array()) {
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$this->Users->request->data = array_merge($data, array('_method' => 'POST'));
	}

/**
 * Test
 *
 * @return void
 */
	private function __setGet() {
		$_SERVER['REQUEST_METHOD'] = 'GET';
	}

/**
 * Test
 *
 * @var string $method unused variable
 * @return void
 */
	public function endTest($method) {
		unset($this->Users);
		ClassRegistry::flush();
	}

}
