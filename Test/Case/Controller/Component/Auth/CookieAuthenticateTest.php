<?php
/**
 * CookieAuthenticateTest file
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Test.Case.Controller.Component.Auth
 * @since         CakePHP(tm) v 2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('ComponentCollection', 'Controller');
App::uses('CookieAuthenticate', 'Users.Controller/Component/Auth');
App::uses('CookieComponent', 'Controller/Component');
App::uses('SessionComponent', 'Controller/Component');
App::uses('AppModel', 'Model');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('Router', 'Routing');

/**
 * Test case for FormAuthentication
 *
 * @package       Cake.Test.Case.Controller.Component.Auth
 */
class CookieAuthenticateTest extends CakeTestCase {

	public $fixtures = array('plugin.users.multi_user');

/**
 * setup
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->request = new CakeRequest('posts/index', false);
		Router::setRequestInfo($this->request);
		$this->Collection = new ComponentCollection();
		$this->Collection->load('Cookie');
		$this->Collection->load('Session');
		$this->auth = new CookieAuthenticate($this->Collection, array(
			'fields' => array('username' => 'user', 'password' => 'password'),
			'userModel' => 'MultiUser',
		));
		$password = Security::hash('password', null, true);
		$User = ClassRegistry::init('MultiUser');
		$User->updateAll(array('password' => $User->getDataSource()->value($password)));
		$this->response = $this->getMock('CakeResponse');
	}

/**
 * tearDown
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		$this->Collection->Cookie->destroy();
	}

/**
 * test authenticate email or username
 *
 * @return void
 */
	public function testAuthenticate() {
		$expected = array(
			'id' => 1,
			'user' => 'mariano',
			'email' => 'mariano@example.com',
			'token' => '12345',
			'created' => '2007-03-17 01:16:23',
			'updated' => '2007-03-17 01:18:31'
		);

		$result = $this->auth->authenticate($this->request, $this->response);
		$this->assertFalse($result);

		$this->Collection->Cookie->write('MultiUser', array('user' => 'mariano', 'password' => 'password'));
		$result = $this->auth->authenticate($this->request, $this->response);
		$this->assertEquals($expected, $result);
	}
}
