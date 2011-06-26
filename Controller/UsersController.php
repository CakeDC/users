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

App::uses('UsersAppController', 'Users.Controller');

/**
 * Users Users Controller
 *
 * @package users
 * @subpackage users.controllers
 */
class UsersController extends UsersAppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Users';

/**
 * Helpers
 *
 * @var array
 */
	public $helpers = array('Html', 'Form', 'Session', 'Time', 'Text');

/**
 * Components
 *
 * @var array
 */
	public $components = array('Auth', 'Session', 'Email', 'Cookie','Paginator');

/**
 * $presetVars
 *
 * @var array $presetVars
 */
	public $presetVars = array(
		array('field' => 'search', 'type' => 'value'),
		array('field' => 'username', 'type' => 'value'),
		array('field' => 'email', 'type' => 'value'));

/**
 * Constructor.
 *
 * @param CakeRequest $request Request object for this controller can be null for testing.
 *  But expect that features that use the params will not work.
 */
	public function __construct($request = null) {
		parent::__construct($request);
		$this->_setupComponents();
		$this->_setupHelpers();
	}

/**
 * Setup components based on plugin availability
 *
 * @return void
 */	
	protected function _setupComponents() {
		if (App::import('Component', 'Search.Prg')) {
			$this->components[] = 'Search.Prg';
		}
	}

/**
 * Setup helpers based on plugin availability
 *
 * @return void
 */	
	protected function _setupHelpers() {
		if (App::import('Helper', 'Goodies.Gravatar')) {
			$this->helpers[] = 'Goodies.Gravatar';
		}
	}

/**
 * beforeFilter callback
 *
 * @return void
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->_setupAuth();

		$this->set('model', $this->modelClass);

		if (!Configure::read('App.defaultEmail')) {
			Configure::write('App.defaultEmail', 'noreply@' . env('HTTP_HOST'));
		}
	}

/**
 * Setup Authentication Component
 *
 * @return void
 */
	public function _setupAuth() {
		$this->Auth->allow('register', 'reset', 'verify', 'logout', 'index', 'view', 'reset_password');

		if ($this->action == 'register') {
			$this->Components->disable('Auth');
		}

		if ($this->action == 'login') {
			$this->Auth->autoRedirect = false;
		}
	}

/**
 * List of all users
 *
 * @return void
 */
	public function index() {
		//$this->User->contain('Detail');
		$searchTerm = '';
		//$this->Prg->commonProcess($this->modelClass, $this->modelClass, 'index', false);

		if (!empty($this->request->params['named']['search'])) {
			if (!empty($this->request->params['named']['search'])) {
				$searchTerm = $this->request->params['named']['search'];
			}
			$this->request->data[$this->modelClass]['search'] = $searchTerm;
		}

		$this->paginate = array(
			'search',
			'limit' => 12,
			'order' => $this->modelClass . '.username ASC',
			'by' => $searchTerm,
			'conditions' => array(
				'OR' => array(
					'AND' => array(
							$this->modelClass . '.active' => 1, 
							$this->modelClass . '.email_verified' => 1))));
		$this->set('users', $this->paginate($this->modelClass));
		$this->set('searchTerm', $searchTerm);

		if (!isset($this->request->params['named']['sort'])) {
			$this->request->params['named']['sort'] = 'username';
		}
	}

/**
 * The homepage of a users giving him an overview about everything
 *
 * @return void
 */
	public function dashboard() {
		$user = $this->User->read(null, $this->Auth->user('id'));
		$this->set('user', $user);
	}

/**
 * Shows a users profile
 *
 * @param string $slug User Slug
 * @return void
 */
	public function view($slug = null) {
		try {
			$this->set('user', $this->User->view($slug));
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage());
			$this->redirect('/');
		}
	}

/**
 * Edit
 *
 * @param string $id User ID
 * @return void
 */
	public function edit() {
		if (!empty($this->request->data)) {
			if ($this->User->Detail->saveSection($this->Auth->user('id'), $this->request->data, 'User')) {
				$this->Session->setFlash(__d('users', 'Profile saved.'));
			} else {
				$this->Session->setFlash(__d('users', 'Could not save your profile.'));
			}
		} else {
			$this->request->data = $this->User->read(null, $this->Auth->user('id'));
		}

		$this->_setLanguages();
	}

/**
 * Admin Index
 *
 * @return void
 */
	public function admin_index() {
//		$this->Prg->commonProcess();
		$this->{$this->modelClass}->data[$this->modelClass] = $this->passedArgs;
        if ($this->{$this->modelClass}->Behaviors->attached('Search.Searchable')) {
        	$parsedConditions = $this->{$this->modelClass}->parseCriteria($this->Users->passedArgs);
        } else {
        	$parsedConditions = array();
        }
        $this->Paginator->settings[$this->modelClass]['conditions'] = $parsedConditions;
		$this->Paginator->settings[$this->modelClass]['order'] = array($this->modelClass . '.created' => 'desc');

		$this->{$this->modelClass}->recursive = 0;
		$this->set('users', $this->paginate());
	}

/**
 * Admin view
 *
 * @param string $id User ID
 * @return void
 */
	public function admin_view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__d('users', 'Invalid User.'));
			$this->redirect(array('action' => 'index'));
		}
		$this->set('user', $this->User->read(null, $id));
	}

/**
 * Admin add
 *
 * @return void
 */
	public function admin_add() {
		if ($this->User->add($this->request->data)) {
			$this->Session->setFlash(__d('users', 'The User has been saved'));
			$this->redirect(array('action' => 'index'));
		}
	}

/**
 * Admin edit
 *
 * @param string $id User ID
 * @return void
 */
	public function admin_edit($userId = null) {
		try {
			$result = $this->User->edit($userId, $this->request->data);
			if ($result === true) {
				$this->Session->setFlash(__d('users', 'User saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->request->data = $result;
			}
		} catch (OutOfBoundsException $e) {
			$this->Session->setFlash($e->getMessage());
			$this->redirect(array('action' => 'index'));
		}

		if (empty($this->request->data)) {
			$this->request->data = $this->User->read(null, $userId);
		}
	}

/**
 * Delete a user account
 *
 * @param string $userId User ID
 * @return void
 */
	public function admin_delete($userId = null) {
		if ($this->User->delete($userId)) {
			$this->Session->setFlash(__d('users', 'User deleted'));
		} else {
			$this->Session->setFlash(__d('users', 'Invalid User'));
		}

		$this->redirect(array('action' => 'index'));
	}

/**
 * Search for a user
 *
 * @return void
 */
	public function admin_search() {
		$this->search();
	}

/**
 * User register action
 *
 * @return void
 */
	public function register() {
		if ($this->Auth->user()) {
			$this->Session->setFlash(__d('users', 'You are already registered and logged in!'));
			$this->redirect('/');
		}

		if (!empty($this->request->data)) {
			$user = $this->User->register($this->request->data);
			if ($user !== false) {
				$this->set('user', $user);
				$this->_sendVerificationEmail($user[$this->modelClass]['email']);
				$this->Session->setFlash(__d('users', 'Your account has been created. You should receive an e-mail shortly to authenticate your account. Once validated you will be able to login.'));
				$this->redirect(array('action' => 'login'));
			} else {
				unset($this->request->data[$this->modelClass]['password']);
				unset($this->request->data[$this->modelClass]['temppassword']);
				$this->Session->setFlash(__d('users', 'Your account could not be created. Please, try again.'), 'default', array('class' => 'message warning'));
			}
		}

		$this->_setLanguages();
	}

/**
 * Common login action
 *
 * @return void
 */
	public function login() {
		$this->request->is('post') && $this->Auth->login();
		if ($this->Auth->user()) {
			$this->User->id = $this->Auth->user('id');
			$this->User->saveField('last_login', date('Y-m-d H:i:s'));

			if ($this->here == $this->Auth->loginRedirect) {
				$this->Auth->loginRedirect = '/';
			}

			$this->Session->setFlash(sprintf(__d('users', '%s you have successfully logged in'), $this->Auth->user('username')));
			if (!empty($this->request->data)) {
				$data = $this->request->data[$this->modelClass];
				$this->_setCookie();
			}

			if (empty($data['return_to'])) {
				$data['return_to'] = null;
			}
			$this->redirect($this->Auth->redirect($data['return_to']));
		} else {
			$this->Session->setFlash(__d('users', 'Invalid e-mail / password combination.  Please try again', true), null, null, 'auth');
		}

		if (isset($this->request->params['named']['return_to'])) {
			$this->set('return_to', urldecode($this->request->params['named']['return_to']));
		} else {
			$this->set('return_to', false);
		}
	}

/**
 * Search
 *
 * @return void
 */
	// public function search() {
	// 	$searchTerm = '';
	// 	$this->Prg->commonProcess($this->modelClass, $this->modelClass, 'search', false);
	// 
	// 	if (!empty($this->request->params['named']['search'])) {
	// 		$searchTerm = $this->request->params['named']['search'];
	// 		$by = 'any';
	// 	}
	// 	if (!empty($this->request->params['named']['username'])) {
	// 		$searchTerm = $this->request->params['named']['username'];
	// 		$by = 'username';
	// 	}
	// 	if (!empty($this->request->params['named']['email'])) {
	// 		$searchTerm = $this->request->params['named']['email'];
	// 		$by = 'email';
	// 	}
	// 	$this->request->data[$this->modelClass]['search'] = $searchTerm;
	// 
	// 	$this->paginate = array(
	// 		'search',
	// 		'limit' => 12,
	// 		'by' => $by,
	// 		'search' => $searchTerm,
	// 		'conditions' => array(
	// 				'AND' => array(
	// 					$this->modelClass . '.active' => 1,
	// 					$this->modelClass . '.email_verified' => 1)));
	// 
	// 	$this->set('users', $this->paginate($this->modelClass));
	// 	$this->set('searchTerm', $searchTerm);
	// }

/**
 * Common logout action
 *
 * @return void
 */
	public function logout() {
		$message = sprintf(__d('users', '%s you have successfully logged out'), $this->Auth->user('username'));
		$this->Session->destroy();
		$this->Cookie->destroy();
		
		$this->Session->setFlash($message);
		$this->redirect($this->Auth->logout());
	}

/**
 * Confirm email action
 *
 * @param string $type Type
 * @return void
 */
	public function verify($type = 'email') {
		if (isset($this->passedArgs['1'])){
			$token = $this->passedArgs['1'];
		} else {
			$this->redirect(array('action' => 'login'), null, true);
		}

		if ($type === 'email') {
			$data = $this->User->validateToken($token);
		} elseif($type === 'reset') {
			$data = $this->User->validateToken($token, true);
		} else {
			$this->Session->setFlash(__d('users', 'There url you accessed is not longer valid'));
			$this->redirect('/');
		}

		if ($data !== false) {
			$email = $data[$this->modelClass]['email'];
			unset($data[$this->modelClass]['email']);

			if ($type === 'reset') {
				$newPassword = $data[$this->modelClass]['password'];
				$data[$this->modelClass]['password'] = $this->Auth->password($newPassword);
			}

			if ($type === 'email') {
				$data[$this->modelClass]['active'] = 1;
			}

			if ($this->User->save($data, false)) {
				if ($type === 'reset') {
					$this->Email->to = $email;
					$this->Email->from = Configure::read('App.defaultEmail');
					$this->Email->replyTo = Configure::read('App.defaultEmail');
					$this->Email->return = Configure::read('App.defaultEmail');
					$this->Email->subject = env('HTTP_HOST') . ' ' . __d('users', 'Password Reset');
					$this->Email->template = null;
					$content[] = __d('users', 'Your password has been reset');
					$content[] = __d('users', 'Please login using this password and change your password');
					$content[] = $newPassword;
					$this->Email->send($content);
					$this->Session->setFlash(__d('users', 'Your password was sent to your registered email account'));
					$this->redirect(array('action' => 'login'));
				} else {
					unset($data);
					$data[$this->modelClass]['active'] = 1;
					$this->User->save($data);
					$this->Session->setFlash(__d('users', 'Your e-mail has been validated!'));
					$this->redirect(array('action' => 'login'));
				}
			} else {
				$this->Session->setFlash(__d('users', 'There was an error trying to validate your e-mail address. Please check your e-mail for the URL you should use to verify your e-mail address.'));
				$this->redirect('/');
			}
		} else {
			$this->Session->setFlash(__d('users', 'The url you accessed is not longer valid'));
			$this->redirect('/');
		}
	}

/**
 * Allows the user to enter a new password, it needs to be confirmed
 *
 * @return void
 */
	public function change_password() {
                if ($this->request->is('post')) {
			$this->request->data[$this->modelClass]['id'] = $this->Auth->user('id');
			if ($this->User->changePassword($this->request->data)) {
                                $this->Session->setFlash(__d('users', 'Password changed.'));
				$this->redirect('/');
			}
		}
	}

/**
 * Reset Password Action
 *
 * Handles the trigger of the reset, also takes the token, validates it and let the user enter
 * a new password.
 *
 * @param string $token Token
 * @param string $user User Data
 * @return void
 */
	public function reset_password($token = null, $user = null) {
		if (empty($token)) {
			$admin = false;
			if ($user) {
				$this->request->data = $user;
				$admin = true;
			}
			$this->_sendPasswordReset($admin);
		} else {
			$this->__resetPassword($token);
		}
	}

/**
 * Sets a list of languages to the view which can be used in selects
 *
 * @param string View variable name, default is languages
 * @return void
 */
	protected function _setLanguages($viewVar = 'languages') {
		if (!App::import('Lib', 'Utils.Languages')) {
			return false;
		}
		$Languages = new Languages();
		$this->set($viewVar, $Languages->lists('locale'));
	}

/**
 * Sends the verification email
 *
 * This method is protected and not private so that classes that inherit this
 * controller can override this method to change the varification mail sending
 * in any possible way.
 *
 * @param string $to Receiver email address
 * @param array $options EmailComponent options
 * @return boolean Success
 */
	protected function _sendVerificationEmail($to = null, $options = array()) {
		$defaults = array(
			'from' => 'noreply@' . env('HTTP_HOST'),
			'subject' => __d('users', 'Account verification'),
			'template' => 'Users.account_verification');

		$options = array_merge($defaults, $options);

		$this->Email->to = $to;
		$this->Email->from = $options['from'];
		$this->Email->subject = $options['subject'];
		$this->Email->template = $options['template'];

		return $this->Email->send();
	}

/**
 * Checks if the email is in the system and authenticated, if yes create the token
 * save it and send the user an email
 *
 * @param boolean $admin Admin boolean
 * @param array $options Options
 * @return void
 */
	protected function _sendPasswordReset($admin = null, $options = array()) {
		$defaults = array(
			'from' => 'noreply@' . env('HTTP_HOST'),
			'subject' => __d('users', 'Password Reset'),
			'template' => 'Users.password_reset_request');

		$options = array_merge($defaults, $options);

		if (!empty($this->request->data)) {
			$user = $this->User->passwordReset($this->request->data);

			if (!empty($user)) {
				$this->set('token', $user[$this->modelClass]['password_token']);
				$this->Email->to = $user[$this->modelClass]['email'];
				$this->Email->from = $options['from'];
				$this->Email->subject = $options['subject'];
				$this->Email->template = $options['template'];

				$this->Email->send();
				if ($admin) {
					$this->Session->setFlash(sprintf(
						__d('users', '%s has been sent an email with instruction to reset their password.'),
						$user[$this->modelClass]['email']));
					$this->redirect(array('action' => 'index', 'admin' => true));
				} else {
					$this->Session->setFlash(__d('users', 'You should receive an email with further instructions shortly'));
					$this->redirect(array('action' => 'login'));
				}
			} else {
				$this->Session->setFlash(__d('users', 'No user was found with that email.'));
				$this->redirect($this->referer('/'));
			}
		}
		$this->render('request_password_change');
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
		if (empty($this->request->data[$this->modelClass]['remember_me'])) {
			$this->Cookie->delete($cookieKey);
		} else {
			$validProperties = array('domain', 'key', 'name', 'path', 'secure', 'time');
			$defaults = array(
				'name' => 'rememberMe');

			$options = array_merge($defaults, $options);
			foreach ($options as $key => $value) {
				if (in_array($key, $validProperties)) {
					$this->Cookie->{$key} = $value;
				}
			}

			$cookieData = array();
                        $cookieData['username'] = $this->request->data[$this->modelClass]['username'];
			$cookieData['password'] = $this->request->data[$this->modelClass]['password'];
			$this->Cookie->write($cookieKey, $cookieData, true, '1 Month');
		}
		unset($this->request->data[$this->modelClass]['remember_me']);
	}

/**
 * This method allows the user to change his password if the reset token is correct
 *
 * @param string $token Token
 * @return void
 */
	private function __resetPassword($token) {
		$user = $this->User->checkPasswordToken($token);
		if (empty($user)) {
			$this->Session->setFlash(__d('users', 'Invalid password reset token, try again.'));
			$this->redirect(array('action' => 'reset_password'));
		}

		if (!empty($this->request->data) && $this->User->resetPassword(Set::merge($user, $this->request->data))) {
			$this->Session->setFlash(__d('users', 'Password changed, you can now login with your new password.'));
			$this->redirect($this->Auth->loginAction);
		}

		$this->set('token', $token);
	}
}
