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
			'loginAction' => array(
				'plugin' => 'users',
				'controller' => 'users',
				'action' => 'login',
				'prefix' => 'admin',
				'admin' => false),
			'loginRedirect' => $this->Session->read('Auth.redirect'),
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
}
