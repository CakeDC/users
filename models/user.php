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

/**
 * Users Plugin User Model
 *
 * @package users
 * @subpackage users.models
 */
class User extends UsersAppModel {

/**
 * Name
 *
 * @var string
 */
	public $name = 'User';

/**
 * Behaviors
 *
 * @var array
 */
	public $actsAs = array(
		'Search.Searchable',
		'Utils.Sluggable' => array(
			'label' => 'username',
			'method' => 'multibyteSlug'));

/**
 * Additional Find methods
 *
 * @var array
 */
	public $_findMethods = array('search' => true);

/**
 * @todo comment me
 *
 * @var array
 */
	public $filterArgs = array(
		array('name' => 'username', 'type' => 'string'),
		array('name' => 'email', 'type' => 'string'));

/**
 * Displayfield
 *
 * @var string $displayField
 */
	public $displayField = 'username';

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'Detail' => array(
			'className' => 'Users.Detail',
			'foreign_key' => 'user_id'));

/**
 * Validation parameters
 *
 * @var array
 */
	public $validate = array();

/**
 * Detail model
 *
 * @var Detail
 */
	public $Detail = null;

/**
 * Constructor
 *
 * @param string $id ID
 * @param string $table Table
 * @param string $ds Datasource
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->validate = array(
			'username' => array(
				'required' => array(
					'rule' => array('notEmpty'),
					'required' => true, 'allowEmpty' => false,
					'message' => __d('users', 'Please enter a username', true)),
				'alpha' => array(
					'rule'=>array('alphaNumeric'), 
					'message' => __d('users', 'The username must be alphanumeric', true)),
				'unique_username' => array(
					'rule'=>array('isUnique','username'),
					'message' => __d('users', 'This username is already in use.', true)),
				'username_min' => array(
					'rule' => array('minLength', '3'),
					'message' => __d('users', 'The username must have at least 3 characters.', true))),
			'email' => array(
				'isValid' => array(
					'rule' => 'email',
					'required' => true,
					'message' => __d('users', 'Please enter a valid email address.', true)),
				'isUnique' => array(
					'rule' => array('isUnique','email'),
					'message' => __d('users', 'This email is already in use.', true))),
			'passwd' => array(
				'to_short' => array(
					'rule' => array('minLength', '4'),
					'message' => __d('users', 'The password must have at least 8 characters.', true)),
				'required' => array(
					'rule' => 'notEmpty',
					'message' => __d('users', 'Please enter a password.', true))),
			'temppassword' => array(
				'rule' => 'confirmPassword',
				'message' => __d('users', 'The passwords are not equal, please try again.', true)),
			'tos' => array(
				'rule' => array('custom','[1]'),
				'message' => __d('users', 'You must agree to the terms of use.', true)));

		$this->validatePasswordChange = array(
			'new_password' => $this->validate['passwd'],
			'confirm_password' => array(
				'required' => array('rule' => array('compareFields', 'new_password', 'confirm_password'), 'required' => true, 'message' => __d('users', 'The passwords are not equal.', true))),
			'old_password' => array(
				'to_short' => array('rule' => 'validateOldPassword', 'required' => true, 'message' => __d('users', 'Invalid password.', true))));

		$this->Detail->sectionSchema[$this->alias] = array(
			'birthday' => array(
				'type' => 'date',
				'null' => null,
				'default' => null,
				'length' => null));

		$this->Detail->sectionValidation[$this->alias] = array(
			'birthday' => array(
				'validDate' => array('rule' => array('date'), 'allowEmpty' => true, 'message' => __d('users', 'Invalid date', true))));
	}

/**
 * After save callback
 *
 * @param boolean $created
 * @return void
 */
	public function afterSave($created) {
		if ($created) {
			if (!empty($this->data[$this->alias]['slug'])) {
				if ($this->hasField('url')) {
					$this->saveField('url', '/user/' . $this->data[$this->alias]['slug'], false);
				}
			}
		}
	}

/**
 * afterFind callback
 *
 * @param array $results Result data
 * @param mixed $primary Primary query
 * @return array
 */
	public function afterFind($results, $primary = false) {
		foreach ($results as &$row) {
			if (isset($row['Detail']) && (is_array($row))) {
				$row['Detail'] = $this->Detail->getSection($row[$this->alias]['id'], $this->alias);
			}
		}
		return $results;
	}

/**
 * Custom validation method to ensure that the two entered passwords match
 *
 * @param string $password Password
 * @return boolean Success
 */
	public function confirmPassword($password = null) {
		if ((isset($this->data[$this->alias]['passwd']) && isset($password['temppassword']))
			&& !empty($password['temppassword'])
			&& ($this->data[$this->alias]['passwd'] === $password['temppassword'])) {
			return true;
		}
		return false;
	}

/**
 * Compares the email confirmation
 *
 * @param array $email Email data
 * @return boolean
 */
	public function confirmEmail($email = null) {
		if ((isset($this->data[$this->alias]['email']) && isset($email['confirm_email']))
			&& !empty($email['confirm_email'])
			&& (strtolower($this->data[$this->alias]['email']) === strtolower($email['confirm_email']))) {
				return true;
		}
		return false;
	}

/**
 * Validates the user token
 *
 * @param string $token Token
 * @param boolean $reset Reset boolean
 * @param boolean $now time() value
 * @return mixed false or user data
 */
	public function validateToken($token = null, $reset = false, $now = null) {
		if (!$now) {
			$now = time();
		}

		$this->recursive = -1;
		$data = false;
		$match = $this->find(array(
			$this->alias . '.email_token' => $token),
			'id, email, email_token_expires');

		if (!empty($match)){
			$expires = strtotime($match[$this->alias]['email_token_expires']);
			if ($expires > $now) {
				$data[$this->alias]['id'] = $match[$this->alias]['id'];
				$data[$this->alias]['email'] = $match[$this->alias]['email'];
				$data[$this->alias]['email_authenticated'] = '1';

				if ($reset === true) {
					$data[$this->alias]['passwd'] = $this->generatePassword();
					$data[$this->alias]['password_token'] = null;
				}

				$data[$this->alias]['email_token'] = null;
				$data[$this->alias]['email_token_expires'] = null;
			}
		}
		return $data;
	}

/**
 * Updates the last activity field of a user
 *
 * @param string $user User ID
 * @return boolean True on success
 */
	public function updateLastActivity($userId = null) {
		if (!empty($userId)) {
			$this->id = $userId;
		}
		if ($this->exists()) {
			return $this->saveField('last_activity', date('Y-m-d H:i:s', time()));
		}
		return false;
	}

/**
 * Checks if an email is in the system, validated and if the user is active so that the user is allowed to reste his password
 *
 * @param array $postData post data from controller
 * @return mixed False or user data as array on success
 */
	public function passwordReset($postData = array()) {
		$user = $this->find('first', array(
			'conditions' => array(
				$this->alias . '.active' => 1,
				$this->alias . '.email' => $postData[$this->alias]['email'])));

		if (!empty($user) && $user[$this->alias]['email_authenticated'] == 1) {
			$sixtyMins = time() + 43000;
			$token = $this->generateToken();
			$user[$this->alias]['password_token'] = $token;
			$user[$this->alias]['email_token_expires'] = date('Y-m-d H:i:s', $sixtyMins);
			$user = $this->save($user, false);
			return $user;
		} elseif (!empty($user) && $user[$this->alias]['email_authenticated'] == 0){
			$this->invalidate('email', __d('users', 'This Email Address exists but was never validated.', true));
		} else {
			$this->invalidate('email', __d('users', 'This Email Address does not exist in the system.', true));
		}
		return false;
	}

/**
 * Checks the token for a password change
 * 
 * @param string $token Token
 * @return mixed False or user data as array
 */
	public function checkPasswordToken($token = null) {
		$user = $this->find('first', array(
			'contain' => array(),
			'conditions' => array(
				$this->alias . '.active' => 1,
				$this->alias . '.password_token' => $token,
				$this->alias . '.email_token_expires >=' => date('Y-m-d H:i:s'))));
		if (empty($user)) {
			return false;
		}
		return $user;
	}

/**
 * Resets the password
 * 
 * @param array $postData Post data from controller
 * @return boolean True on success
 */
	public function resetPassword($postData = array()) {
		$result = false;
		$tmp = $this->validate;
		$this->validate = array(
			'new_password' => $this->validate['passwd'],
			'confirm_password' => array(
				'required' => array(
					'rule' => array('compareFields', 'new_password', 'confirm_password'), 
					'message' => __d('users', 'The passwords are not equal.', true))));

		$this->set($postData);
		if ($this->validates()) {
			App::import('Core', 'Security');
			$this->data[$this->alias]['passwd'] = Security::hash($this->data[$this->alias]['new_password'], null, true);
			$this->data[$this->alias]['password_token'] = null;
			$result = $this->save($this->data, false);
		}
		$this->validate = $tmp;
		return $result;
	}

/**
 * Changes the password for a user
 *
 * @param array $postData Post data from controller
 * @return boolean True on success
 */
	public function changePassword($postData = array()) {
		$this->set($postData);
		//$tmp = $this->validate;
		$this->validate = $this->validatePasswordChange;

		if ($this->validates()) {
			App::import('Core', 'Security');
			$this->data[$this->alias]['passwd'] = Security::hash($this->data[$this->alias]['new_password'], null, true);
			$this->save($postData, array(
				'validate' => false,
				'callbacks' => false));
			//$this->validate = $tmp;
			return true;
		}

		//$this->validate = $tmp;
		return false;
	}

/**
 * Validation method to check the old password
 *
 * @param array $password 
 * @return boolean True on success
 */
	public function validateOldPassword($password) {
		if (!isset($this->data[$this->alias]['id']) || empty($this->data[$this->alias]['id'])) {
			if (Configure::read('debug') > 0) {
				throw new OutOfBoundsException(__d('users', '$this->data[\'' . $this->alias . '\'][\'id\'] has to be set and not empty', true));
			}
		}

		$passwd = $this->field('passwd', array($this->alias . '.id' => $this->data[$this->alias]['id']));
		App::import('Core', 'Security');
		if ($passwd === Security::hash($password['old_password'], null, true)) {
			return true;
		}
		return false;
	}

/**
 * Validation method to compare two fields
 *
 * @param mixed $field1 Array or string, if array the first key is used as fieldname
 * @param string $field2 Second fieldname
 * @return boolean True on success
 */
	public function compareFields($field1, $field2) {
		if (is_array($field1)) {
			$field1 = key($field1);
		}
		if (isset($this->data[$this->alias][$field1]) && isset($this->data[$this->alias][$field2]) && 
			$this->data[$this->alias][$field1] == $this->data[$this->alias][$field2]) {
			return true;
		}
		return false;
	}

/**
 * Returns all data about a user
 *
 * @param string $slug user slug
 * @return array
 */
	public function view($slug = null) {
		$user = $this->find('first', array(
			'contain' => array(
				//'Tag',
				'Detail'),
			'conditions' => array(
				$this->alias . '.slug' => $slug,
				$this->alias . '.active' => 1,
				$this->alias . '.email_authenticated' => 1),
		));

		if (empty($user)) {
			throw new Exception(__d('users', 'The user does not exist.', true));
		}
		return $user;
	}

/**
 * Registers a new user
 *
 * @param array $postData Post data from controller
 * @param boolean $useEmailVerification If set to true a token will be generated
 * @return mixed
 */
	public function register($postData = array(), $useEmailVerification = true) {
		if ($useEmailVerification == true) {
			$postData[$this->alias]['email_token'] = $this->generateToken();
			$postData[$this->alias]['email_token_expires'] = date('Y-m-d H:i:s', time() + 86400);
		} else {
			$postData[$this->alias]['email_authenticated'] = 1;
		}
		$postData[$this->alias]['active'] = 1;

		$this->_removeExpiredRegistrations();

		$this->set($postData);
		if ($this->validates()) {
			App::import('Core', 'Security');
			$postData[$this->alias]['passwd'] = Security::hash($postData[$this->alias]['passwd'], 'sha1', true);
			$this->create();
			return $this->save($postData, false);
		}

		return false;
	}

/**
 * Resends the verification if the user is not already validated or invalid
 *
 * @param array $postData Post data from controller
 * @return mixed False or user data array on success
 */
	public function resendVerification($postData = array()) {
		if (!isset($postData[$this->alias]['email']) || empty($postData[$this->alias]['email'])) {
			$this->invalidate('email', __d('users', 'Please enter your email address.', true));
			return false;
		}

		$user = $this->find('first', array(
			'contain' => array(),
			'conditions' => array(
				$this->alias . '.email' => $postData[$this->alias]['email'])));

		if (empty($user)) {
			$this->invalidate('email', __d('users', 'The email address does not exist in the system', true));
			return false;
		}

		if ($user[$this->alias]['email_authenticated'] == 1) {
			$this->invalidate('email', __d('users', 'Your account is already authenticaed.', true));
			return false;
		}

		if ($user[$this->alias]['active'] == 0) {
			$this->invalidate('email', __d('users', 'Your account is disabled.', true));
			return false;
		}

		$user[$this->alias]['email_token'] = $this->generateToken();
		$user[$this->alias]['email_token_expires'] = date('Y-m-d H:i:s', time() + 86400);

		return $this->save($user, false);
	}

/**
 * Generates a password
 *
 * @param int $length Password length
 * @return string
 */
	public function generatePassword($length = 10) {
		srand((double)microtime() * 1000000);
		$password = '';
		$vowels = array("a", "e", "i", "o", "u");
		$cons = array("b", "c", "d", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "u", "v", "w", "tr",
							"cr", "br", "fr", "th", "dr", "ch", "ph", "wr", "st", "sp", "sw", "pr", "sl", "cl");
		for ($i = 0; $i < $length; $i++) {
			$password .= $cons[mt_rand(0, 31)] . $vowels[mt_rand(0, 4)];
		}
		return substr($password, 0, $length);
	}

/**
 * Generate token used by the user registration system
 *
 * @param int $length Token Length
 * @return string
 */
	public function generateToken($length = 10) {
		$possible = '0123456789abcdefghijklmnopqrstuvwxyz';
		$token = "";
		$i = 0;

		while ($i < $length) {
			$char = substr($possible, mt_rand(0, strlen($possible) - 1), 1);
			if (!stristr($token, $char)) {
				$token .= $char;
				$i++;
			}
		}
		return $token;
	}

/**
 * Returns the search data
 *
 * @param string $state Find State
 * @param string $query Query options
 * @param string $results Result data
 * @return array
 */
	protected function _findSearch($state, $query, $results = array()) {
		if ($state == 'before') {
			$this->Behaviors->attach('Containable', array('autoFields' => false));
			$results = $query;
			if (!empty($query['by'])) {
				$by = $query['by'];
			}

			if (empty($query['search'])) {
				$query['search'] = '';
			}

			$db =& ConnectionManager::getDataSource($this->useDbConfig);
			$by = $query['by'];
			$search = $query['search'];
			$byQuoted = $db->value($search);
			$like = '%' . $query['search'] . '%';

			switch ($by) {
				case 'username':
					$results['conditions'] = Set::merge(
						$query['conditions'],
						array($this->alias . '.username LIKE' => $like));
					break;
				case 'email':
					$results['conditions'] = Set::merge(
						$query['conditions'],
						array($this->alias . '.email LIKE' => $like));
					break;
				case 'any':
					$results['conditions'] = Set::merge(
						$query['conditions'],
						array('OR' => array(
							array($this->alias . '.username LIKE' => $like),
							array($this->alias . '.email LIKE' => $like))));
					break;
				case '' :
					$results['conditions'] = $query['conditions'];
					break;
				default :
					$results['conditions'] = Set::merge(
						$query['conditions'],
						array($this->alias . '.username LIKE' => $like));
					break;
			}

			if (isset($query['operation']) && $query['operation'] == 'count') {
				$results['fields'] = array('COUNT(DISTINCT ' . $this->alias . '.id)');
			} else {
				//$results['fields'] = array('DISTINCT User.*');
			}
			return $results;
		} elseif ($state == 'after') {
			if (isset($query['operation']) && $query['operation'] == 'count') {
				if (isset($query['group']) && is_array($query['group']) && !empty($query['group'])) {
					return count($results);
				}
				return $results[0][0]['COUNT(DISTINCT ' . $this->alias . '.id)'];
			}
			return $results;
		}
	}

/**
 * Customized paginateCount method
 *
 * @param array $conditions Find conditions
 * @param int $recursive Recursive level
 * @param array $extra Extra options
 * @return array
 */
	function paginateCount($conditions = array(), $recursive = 0, $extra = array()) {
		$parameters = compact('conditions');
		if ($recursive != $this->recursive) {
			$parameters['recursive'] = $recursive;
		}
		if (isset($extra['type']) && isset($this->_findMethods[$extra['type']])) {
			$extra['operation'] = 'count';
			return $this->find($extra['type'], array_merge($parameters, $extra));
		} else {
			return $this->find('count', array_merge($parameters, $extra));
		}
	}

/**
 * Adds a new user
 * 
 * @param array post data, should be Controller->data
 * @return array
 */
	public function add($postData = null) {
		if (!empty($postData)) {
			$this->create();
			if ($this->save($postData)) {
				return true;
			}
		}
	}

/**
 * Edits an existing user
 *
 * @param string $userId User ID
 * @param array $postData controller post data usually $this->data
 * @return mixed True on successfully save else post data as array
 */
	public function edit($userId = null, $postData = null) {
		$user = $this->find('first', array(
			'contain' => array(
				'Detail'),
			'conditions' => array(
				$this->alias . '.id' => $userId)));

		$this->set($user);
		if (empty($user)) {
			throw new OutOfBoundsException(__d('users', 'Invalid User', true));
		}

		if (!empty($postData)) {
			$this->set($postData);
			$result = $this->save(null, true);
			if ($result) {
				$this->data = $result;
				return true;
			} else {
				return $postData;
			}
		}
	}

/**
 * Removes all users from the user table that are outdated
 *
 * Override it as needed for your specific project
 *
 * @return void
 */
	protected function _removeExpiredRegistrations() {
		$this->deleteAll(array(
			$this->alias . '.email_authenticated' => 0,
			$this->alias . '.email_token_expires <' => date('Y-m-d H:i:s')));
	}
}
