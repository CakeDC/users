<?php
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
		'Cookie');

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
			'name' => 'Users'),
		'fields' => array(
			'email',
			'username',
			'password',
		),
	);

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
 *
 *
 * @param Controller $controller
 * @return void
 */
	public function startup(Controller $controller) {
		$this->Controller = $controller;
		$this->request = $this->Controller->request;
		$this->response = $this->Controller->response;
		$this->Auth = $this->Controller->Auth;

		if ($this->settings['autoLogin'] == true && !$this->Auth->loggedIn()) {
			$this->restoreLoginFromCookie();
		}
	}

/**
 * Logs the user again in based on the cookie data
 *
 * @return boolean True on login success, false on failure
 */
	public function restoreLoginFromCookie() {
		extract($this->settings);
		$cookie = $this->Cookie->read($cookieKey);

		if (!empty($cookie)) {
			foreach ($fields as $field) {
				if (!empty($cookie[$field])) {
					$this->request->data[$userModel][$field] = $cookie[$field];
				}
			}
			return $this->Auth->login();
		}
	}

/**
 * Sets the cookie with the specified fields
 *
 * @param options
 * @return void
 */
	public function setCookie() {
		extract($this->settings);

		$cookieData = array();
		foreach ($fields as $field) {
			if (isset($this->request->data[$userModel][$field]) && !empty($this->request->data[$userModel][$field])) {
				$cookieData[$field] = $this->request->data[$userModel][$field];
			}
		}

		$this->Cookie->write($cookieKey, $cookieData, true);
	}

	public function destroyCookie() {
		extract($this->settings);
		$this->Cookie->destroy();
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
			'name' => 'Users');

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