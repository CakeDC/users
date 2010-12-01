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

App::import('Component', 'Auth');

/**
 * Users plugin Auth component
 *
 * Provides additional functionality over the core Auth component such as cookie
 * logins.
 *
 * @package users
 * @subpackage users.controllers.components
 */
class UsersAuthComponent extends AuthComponent {

/**
 * Component dependencies
 *
 * @var array
 */
	public $components = array(
		'Auth',
		'Cookie',
		'RequestHandler',
		'Session',
	);

/**
 * Options for cookie storage of authentication information.
 *
 * @var array
 */
	public $cookieOptions = null;

/**
 * Override for Auth::initialize to provide Users plugin defaults.
 *
 * For all settings other than "cookieOptions", please refer to the core Auth component
 *
 * ### Settings:
 *
 * - `cookieOptions`  array  Array of cookie settings:
 *    - `domain`  string  Domain name to restrict cookie. Use '.domain.com' to
 *      allow all subdomains to access the cookie, or just 'domain.com' for the
 *      main domain.
 *    - `name`  string  Cookie name to use
 *    - `key`  string  Cypher key for encrypting cookie data
 *    - `time`  string  Can be a Unix timestamp or a string time, eg: '1 Month'
 *    - `path`  string  Path to limit cookie use, defaults to the controller
 *      'base' which is derived from the CakePHP app install location.
 *    - `secure`  boolean  True if values are to be secured / crypted.
 *
 * @param AppController $controller
 * @param array $settings
 * @return void
 */
	public function initialize(Controller $controller, array $settings = array()) {
		// For legacy support, add the 'Auth' variable on the controller, so
		// apps can still reference this auth component as if it were the core
		// auth component.
		$controller->Auth = $this;

		$loginRedirect = $this->Session->read('Auth.redirect');
		if (empty($loginRedirect)) {
			$loginRedirect = array(
				'admin' => false,
				'prefix' => 'admin',
				'plugin' => 'users',
				'controller' => 'users',
				'action' => 'dashboard');
		}

		$defaults = array(
			'loginAction' => array(
				'admin' => false,
				'prefix' => 'admin',
				'plugin' => 'users',
				'controller' => 'users',
				'action' => 'login'),
			'authorize' => 'controller',
			'fields' => array(
				'username' => 'email',
				'password' => 'passwd'),
			'loginRedirect' => $loginRedirect,
			'logoutRedirect' => '/',
			'authError' => __d('users', 'Sorry, but you need to login to access this location.', true),
			'loginError' => __d('users', 'Invalid e-mail / password combination. Please try again', true),
			'autoRedirect' => true,
			'userModel' => 'Users.User',
			'userScope' => array(
				$controller->modelClass . '.active' => 1,
				$controller->modelClass . '.email_authenticated' => 1),
			'cookieOptions' => array(
				'domain' => env('HTTP_HOST'),
				'name' => 'Users',
				'keyname' => 'rememberMe',
				'time' => '1 Month',
				'path' => '/' . $controller->base),
		);
		parent::initialize($controller, array_merge($defaults, $settings));
	}

/**
 * Override for Auth::login to provide last login tracking and inject cookie checks and storage
 *
 * @param array $data Form data
 * @return boolean True if login is successful
 */
	public function login($data = null, $skipCookies = false) {
		$loggedIn = 
			parent::login($data) || (
				!$skipCookies &&
				$this->_getCookie()
			);
		if ($loggedIn) {
			$User = $this->getModel();
			$User->id = $this->user($User->primaryKey);
			$User->saveField('last_login', date('Y-m-d H:i:s'));

			$this->Session->setFlash(sprintf(__d('users', '%s, you have successfully logged in.', true), $this->user('username')));
			if (!empty($this->data)) {
				!$skipCookies && $this->_setCookie();
			}
		}
		return $loggedIn;
	}

/**
 * Destroy session and cookie information, and return the logoutRedirect URL
 *
 * @return string logoutRedirect url
 */
	public function logout() {
		$this->_deleteCookie();
		return parent::logout();
	}

/**
 * Setup the cookies with options provided the the UsersAuth setup.
 *
 * @return void
 */
	protected function _setupCookies() {
		// Allow only specified values set on the cookie
		$validProperties = array('domain', 'key', 'name', 'path', 'secure', 'time');
		$options = array_intersect_key($this->cookieOptions, array_flip($validProperties));
		foreach ($options as $key => $value) {
			$this->Cookie->{$key} = $value;
		}
	}

/**
 * Sets the cookie to remember the user
 *
 * @param array Cookie component properties as array, like array('domain' => 'yourdomain.com')
 * @param string Cookie data keyname for the userdata, its default is "User". This is set to User and NOT using the model alias to make sure it works with different apps with different user models across different (sub)domains.
 * @return void
 * @link http://api13.cakephp.org/class/cookie-component
 */
	protected function _setCookie() {
		$this->_setupCookies();
		list($plugin, $model) = pluginSplit($this->userModel);
		if (!isset($this->data[$model]['remember_me']) || !$this->data[$model]['remember_me']) {
			$this->Cookie->delete($this->cookieOptions['keyname']);
			return;
		}

		$cookieData = array_intersect_key($this->data[$model], array_flip(array($this->fields['username'], $this->fields['password'])));
		$this->Cookie->write($this->cookieOptions['keyname'], $cookieData);
	}

/**
 * Attempts to automatically login the user if a valid cookie exists
 *
 * @return boolean True if successfully logged in
 */
	protected function _getCookie() {
		$this->_setupCookies();
		$cookieData = $this->Cookie->read($this->cookieOptions['keyname']);

		if ($cookieData === null) {
			return false;
		}
		list($plugin, $model) = pluginSplit($this->userModel);
		return $this->login(array($model => $cookieData), true);
	}

/**
 * Delete the remember cookie.
 *
 * @return void
 */
	protected function _deleteCookie() {
		$this->_setupCookies();
		$this->Cookie->delete($this->cookieOptions['keyname']);
	}
}
