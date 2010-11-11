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
 * - `cookieOptions`  mixed  False to not use cookies, or array of cookie settings:
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
	public function initialize(AppController $controller, array $settings = array()) {
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
			'userModel' => $controller->modelClass,
			'userScope' => array(
				$controller->modelClass . '.active' => 1,
				$controller->modelClass . '.email_authenticated' => 1),
			// 'cookie' => false,
			'cookieOptions' => array(
				'domain' => env('HTTP_HOST'),
				'name' => 'rememberMe',
				'time' => '1 Month',
				'path' => $controller->base),
		);
		parent::initialize($controller, array_merge($defaults, $settings));
	}

/**
 * Override for Auth::login to provide last login tracking and inject cookie checks and storage
 *
 * @param array $data Form data
 * @return boolean True if login is successful
 */
	public function login($data = null) {
		$loggedIn = parent::login($data);
		if ($loggedIn) {
			$User = $this->getModel();
			$User->id = $this->user($User->primaryKey);
			$User->saveField('last_login', date('Y-m-d H:i:s'));

			$this->Session->setFlash(sprintf(__d('users', '%s, you have successfully logged in.', true), $this->user('username')));
			if (!empty($this->data)) {
				$data = $this->data[$this->userModel];
				$this->_setCookie();
			}
		}
		return $loggedIn;
	}

/**
 * Sets the cookie to remember the user
 *
 * @param array Cookie component properties as array, like array('domain' => 'yourdomain.com')
 * @param string Cookie data keyname for the userdata, its default is "User". This is set to User and NOT using the model alias to make sure it works with different apps with different user models across different (sub)domains.
 * @return void
 * @link http://api13.cakephp.org/class/cookie-component
 */
	protected function _setCookie($options = array(), $cookieKey = 'User') {
		if (!isset($this->data[$this->userModel]['remember_me']) || !$this->data[$this->userModel]['remember_me']) {
			$this->Cookie->delete($cookieKey);
			return;
		}

		// Allow only specified values set on the cookie
		$validProperties = array('domain', 'key', 'name', 'path', 'secure', 'time');
		$options = array_merge($this->cookieOptions, $options);
		$options = array_intersect_key($options, array_flip($validProperties));
		foreach ($options as $key => $value) {
			$this->Cookie->{$key} = $value;
		}

		$cookieData = array();
		$cookieData[$this->fields['username']] = $this->data[$this->userModel][$this->fields['username']];
		$cookieData[$this->fields['password']] = $this->data[$this->userModel][$this->fields['password']];
		$this->Cookie->write($cookieKey, $cookieData);
	}
}
