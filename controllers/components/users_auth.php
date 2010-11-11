<?php

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
 * Override for Auth::initialize to provide Users plugin defaults.
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
				$controller->modelClass . '.email_authenticated' => 1)
		);
		parent::initialize($controller, array_merge($defaults, $settings));
	}

	public function login($data = null) {
		$loggedIn = parent::login($data);
		if ($loggedIn) {
			$User = $this->getModel();
			$User->id = $this->user($User->primaryKey);
			$User->saveField('last_login', date('Y-m-d H:i:s'));

			// Prevent the user being redirected back to the login form if successfully logged in.
			$url = Router::normalize(isset($this->params['url']['url']) ? $this->params['url']['url'] : '');
// debug($this->params);
// debug($this->loginRedirect);
// debug($url);
			if ($url == $this->loginRedirect) {
				$this->loginRedirect = '/';
			}

			$this->Session->setFlash(sprintf(__d('users', '%s, you have successfully logged in.', true), $this->user('username')));
			if (!empty($this->data)) {
				$data = $this->data[$this->userModel];
				//$this->_setCookie();
			}
// debug($data);
			if (empty($data['return_to'])) {
				$data['return_to'] = null;
			}
debug('here');
			$this->redirect($this->Auth->redirect($data['return_to']));
		}
		return $loggedIn;
	}
}
