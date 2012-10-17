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
		'Auth',
		'Cookie');

/**
 * Request object
 *
 * @var CakeRequest
 */
	public $request;

/**
 * Default settings
 *
 * @var array
 */
	protected $_defaults = array(
		'autoLogin' => true,
		'userModel' => 'User',
		'cookieKey' => 'rememberMe',
		'cookieName' => 'Users',
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
		$this->Controller = $collection->getController();
		$this->request = $this->Controller->request;
	}

/**
 *
 *
 * @param Controller $controller
 * @return void
 */
	public function initialize(Controller $controller) {
		if ($this->settings['autoLogin'] == true && !$this->Auth->loggedIn()) {
			$this->restoreLoginFromCookie();
		}
	}

/**
 *
 */
	public function restoreLoginFromCookie() {
		extract($this->settings);

		$this->Cookie->name = $cookieName;
		$cookie = $this->Cookie->read($cookieKey);

		if (!empty($cookie)) {
			foreach ($fields as $field) {
				if (!empty($cookie[$field])) {
					$this->request->data[$userModel][$field] = $cookie[$field];
				}
			}
			$this->Auth->login();
		}
	}

/**
 *
 */
	public function setCookie($options = array()) {
		extract($this->settings);

		$validProperties = array('domain', 'key', 'name', 'path', 'secure', 'time');
		$defaults = array(
			'name' => 'Users');

		$options = array_merge($defaults, $options);

		foreach ($options as $key => $value) {
			if (in_array($key, $validProperties)) {
				$this->Cookie->{$key} = $value;
			}
		}

		$cookieData = array();
		foreach ($fields as $field) {
			if (isset($this->request->data[$userModel][$field]) && !empty($this->request->data[$userModel][$field])) {
				$cookieData[$field] = $this->request->data[$userModel][$field];
			}
		}

		$this->Cookie->write($cookieKey, $cookieData, true, '1 Month');
	}

	public function destroyCookie() {
		extract($this->settings);
		$this->Cookie->name = $cookieName;
		$this->Cookie->destroy();
	}

}