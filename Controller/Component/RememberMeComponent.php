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

App::uses('Component', 'Controller');

/**
 * RememberMe Component
 *
 * Logs an user back in if the cookie with the credentials is found
 *
 * @property CookieComponent $Cookie
 * @property AuthComponent $Auth
 */
class RememberMeComponent extends Component {

/**
 * Components
 *
 * @var array
 */
	public $components = array(
		'Cookie',
		'Auth');

/**
 * Request object
 *
 * @var CakeRequest
 */
	public $request;

/**
 * Settings
 *
 * @var array
 */
	public $settings = array();

/**
 * Default settings
 *
 * @var array
 */
	protected $_defaults = array(
		'autoLogin' => true,
		'userModel' => 'User',
		'cookieKey' => 'rememberMe',
		'cookie' => array(
			'name' => 'User'),
		'fields' => array(
			'email',
			'username',
			'password'));

/**
 * Constructor
 *
 * @param ComponentCollection $collection A ComponentCollection for this component
 * @param array $settings Array of settings.
 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
		parent::__construct($collection, $settings);
		$this->settings = Set::merge($this->_defaults, $settings);
		$this->configureCookie($this->settings['cookie']);
	}

/**
 * Initializes RememberMeComponent for use in the controller
 *
 * @param Controller $controller A reference to the instantiating controller object
 * @return void
 */
	public function initialize(Controller $controller) {
		$this->request = $controller->request;
	}

/**
 * startup
 *
 * @param Controller $controller
 * @return void
 */
	public function startup(Controller $controller) {
		if ($this->settings['autoLogin'] == true && !$this->Auth->loggedIn()) {
			$this->restoreLoginFromCookie();
		}
	}

/**
 * Logs the user again in based on the cookie data
 *
 * @param boolean $checkLoginStatus
 * @return boolean True on login success, false on failure
 */
	public function restoreLoginFromCookie($checkLoginStatus = true) {
		if ($checkLoginStatus && $this->Auth->loggedIn()) {
			return true;
		}

		extract($this->settings);
		$cookie = $this->Cookie->read($cookieKey);

		if (!empty($cookie)) {
			foreach ($fields as $field) {
				if (!empty($cookie[$field])) {
					$this->request->data[$userModel][$field] = $cookie[$field];
				}
			}

			$result = $this->Auth->login();
			unset($this->request->data[$userModel]);
			return $result;
		}
	}

/**
 * Sets the cookie with the specified fields
 *
 * @param array Optional, login credentials array in the form of Model.field, if empty this->request['<model>'] will be used
 * @return boolean
 */
	public function setCookie($data = array()) {
		extract($this->settings);

		if (empty($data)) {
			$data = $this->request->data;
			if (empty($data)) {
				$data = $this->Auth->user();
			}
		}

		if (empty($data)) {
			return false;
		}

		$cookieData = array();

		foreach ($fields as $field) {
			if (isset($data[$userModel][$field]) && !empty($data[$userModel][$field])) {
				$cookieData[$field] = $data[$userModel][$field];
			}
		}

		return $this->Cookie->write($cookieKey, $cookieData, true, '+99 years');
	}

/**
 * Destroys the remember me cookie
 *
 * @return void
 */
	public function destroyCookie() {
		extract($this->settings);
		if (isset($_COOKIE[$cookie['name']])) {
			$this->Cookie->name = $cookie['name'];
			$this->Cookie->destroy();
		}
	}

/**
 * Configures the cookie component instance
 *
 * @param array $options
 * @throws InvalidArgumentException Thrown if an invalid option key was passed
 * @return void
 */
	public function configureCookie($options = array()) {
		$validProperties = array('domain', 'key', 'name', 'path', 'secure', 'time');
		$defaults = array(
			'time' => '1 month',
			'name' => 'User');

		$options = array_merge($defaults, $options);

		foreach ($options as $key => $value) {
			if (in_array($key, $validProperties)) {
				$this->Cookie->{$key} = $value;
			} else {
				throw new InvalidArgumentException(__('users', 'Invalid options %s', $key));
			}
		}
	}
}