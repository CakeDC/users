<?php
/**
 * Copyright 2010 - 2012, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2012, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Controller', 'Controller');
App::uses('RememberMeComponent', 'Users.Controller/Component');

/**
 * CookieComponentTestController class
 *
 * @package       Cake.Test.Case.Controller.Component
 */
class RememberMeComponentTestController extends Controller {

/**
 * components property
 *
 * @var array
 */
	public $components = array(
		'Users.RememberMe',
		'Auth');
}

class RememberMeComponentTest extends CakeTestCase {

/**
 * Controller property
 *
 * @var CookieComponentTestController
 */
	public $Controller;

/**
 * start
 *
 * @return void
 */
	public function setUp() {
		$_COOKIE = array();
		$this->Controller = new RememberMeComponentTestController(new CakeRequest(), new CakeResponse());
		$this->Controller->constructClasses();

		$this->RememberMe = $this->Controller->RememberMe;
		$this->RememberMe->Cookie = $this->getMock('CookieComponent',
			array(),
			array($this->Controller->Components));
		$this->RememberMe->Auth = $this->getMock('AuthComponent',
			array(),
			array($this->Controller->Components));
	}

/**
 * testSetCookie
 *
 * @return void
 */
	public function testSetCookie() {
		$this->RememberMe->Cookie->expects($this->once())
			->method('write')
			->with('rememberMe', array(
				'email' => 'email',
				'password' => 'password'), true);

		$this->RememberMe->setCookie(array(
			'User' => array(
				'email' => 'email',
				'password' => 'password')));
	}

/**
 * testRestoreLoginFromCookie
 *
 * @return void
 */
	public function testRestoreLoginFromCookie() {
		$this->RememberMe->Cookie->expects($this->once())
			->method('read')
			->with($this->equalTo('rememberMe'))
			->will($this->returnValue(array(
				'email' => 'email',
				'password' => 'password')));

		$this->RememberMe->Auth->expects($this->once())
			->method('login');

		$this->RememberMe->restoreLoginFromCookie();

		$this->assertEqual($this->RememberMe->request->data, array(
			'User' => array(
				'email' => 'email',
				'password' => 'password')));
	}

/**
 * testDestroyCookie
 *
 * @return void
 */
	public function testDestroyCookie() {
		$this->RememberMe->Cookie->expects($this->once())
			->method('destroy')
			->with($this->equalTo('User'));
		$this->RememberMe->destroyCookie();
	}

}