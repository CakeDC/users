<?php
/**
 * Copyright 2010 - 2011, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2011, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Security', 'Utility');
App::uses('UsersAppModel', 'Users.Model');

/**
 * Users Plugin User Model
 *
 * @package User
 * @subpackage User.Model
 */
class User extends UsersAppModel {

/**
 * Name
 *
 * @var string
 */
	public $name = 'User';

/**
 * Additional Find methods
 *
 * @var array
 */
	public $findMethods = array('search' => true);

/**
 * All search fields need to be configured in the Model::filterArgs array.
 *
 * @var array
 * @link https://github.com/CakeDC/search
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
		'UserDetail' => array(
			'className' => 'Users.UserDetail',
			'foreignKey' => 'user_id'));

/**
 * Validation domain for translations 
 *
 * @var string
 */
	public $validationDomain = 'users';

/**
 * Validation parameters
 *
 * @var array
 */
	public $validate = array(
		'username' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'required' => true, 'allowEmpty' => false,
				'message' => 'Please enter a username.'),
			'alpha' => array(
				'rule' => array('alphaNumeric'),
				'message' => 'The username must be alphanumeric.'),
			'unique_username' => array(
				'rule'=>array('isUnique', 'username'),
				'message' => 'This username is already in use.'),
			'username_min' => array(
				'rule' => array('minLength', '3'),
				'message' => 'The username must have at least 3 characters.')),
		'email' => array(
			'isValid' => array(
				'rule' => 'email',
				'required' => true,
				'message' => 'Please enter a valid email address.'),
			'isUnique' => array(
				'rule' => array('isUnique', 'email'),
				'message' => 'This email is already in use.')),
		'password' => array(
			'too_short' => array(
				'rule' => array('minLength', '6'),
				'message' => 'The password must have at least 6 characters.'),
			'required' => array(
				'rule' => 'notEmpty',
				'message' => 'Please enter a password.')),
		'temppassword' => array(
			'rule' => 'confirmPassword',
			'message' => 'The passwords are not equal, please try again.'),
		'tos' => array(
			'rule' => array('custom','[1]'),
			'message' => 'You must agree to the terms of use.'));

/**
 * Constructor
 *
 * @param string $id ID
 * @param string $table Table
 * @param string $ds Datasource
 */
	public function __construct($id = false, $table = null, $ds = null) {
		$this->_setupBehaviors();
		$this->_setupValidation();
		parent::__construct($id, $table, $ds);
	}

/**
 * Setup available plugins
 *
 * This checks for the existence of certain plugins, and if available, uses them.
 *
 * @return void
 * @link https://github.com/CakeDC/search
 * @link https://github.com/CakeDC/utils
 */
	protected function _setupBehaviors() {
		App::uses('SearchableBehavior', 'Search.Model/Behavior');
		if (class_exists('SearchableBehavior')) {
			$this->actsAs[] = 'Search.Searchable';
		}

		App::uses('SluggableBehavior', 'Utils.Model/Behavior');
		if (class_exists('SluggableBehavior') && Configure::read('Users.disableSlugs') !== true) {
			$this->actsAs['Utils.Sluggable'] = array(
				'label' => 'username',
				'method' => 'multibyteSlug');
		}
	}

/**
 * Setup validation rules
 *
 * @return void
 */
	protected function _setupValidation() {
		$this->validatePasswordChange = array(
			'new_password' => $this->validate['password'],
			'confirm_password' => array(
				'required' => array('rule' => array('compareFields', 'new_password', 'confirm_password'), 'required' => true, 'message' => __d('users', 'The passwords are not equal.'))),
			'old_password' => array(
				'to_short' => array('rule' => 'validateOldPassword', 'required' => true, 'message' => __d('users', 'Invalid password.'))));
	}

/**
 * Sets some defaults for the UserDetail model
 *
 * @return void
 */
	public function setupDetail() {
		$this->UserDetail->sectionSchema[$this->alias] = array(
			'birthday' => array(
				'type' => 'date',
				'null' => null,
				'default' => null,
				'length' => null),
			'first_name' => array(
				'type' => 'string',
				'null' => null,
				'default' => null,
				'length' => null),
			'last_name' => array(
				'type' => 'string',
				'null' => null,
				'default' => null,
				'length' => null));

		$this->UserDetail->sectionValidation[$this->alias] = array(
			'birthday' => array(
				'validDate' => array(
					'rule' => array('date'), 'allowEmpty' => true, 'message' => __d('users', 'Invalid date'))),
			'first_name' => array(
				'notEmpty' => array(
					'rule' => array('notEmpty'), 'allowEmpty' => true, 'message' => __d('users', 'Invalid date'))),
			'last_name' => array(
				'notEmpty' => array(
					'rule' => array('notEmpty'), 'allowEmpty' => true, 'message' => __d('users', 'Invalid date'))));
	}

/**
 * After save callback
 *
 * @param boolean $created
 * @return void
 */
	public function afterSave($created) {
		if ($created) {
			$this->sluggedUserUrl();
		}
	}

/**
 * Override this method as needed to generate the url you want
 *
 * @return void
 * @see User::afterSave();
 */
	public function sluggedUserUrl() {
		if (!empty($this->data[$this->alias]['slug'])) {
			if ($this->hasField('url')) {
				$this->saveField('url', '/user/' . $this->data[$this->alias]['slug'], false);
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
			if (isset($row['UserDetail']) && (is_array($row))) {
				$row['UserDetail'] = $this->UserDetail->getSection($row[$this->alias]['id'], $this->alias);
			}
		}
		return $results;
	}

/**
 * Create a hash from string using given method.
 * Fallback on next available method.
 *
 * Override this method to use a different hashing method
 *
 * @param string $string String to hash
 * @param string $type Method to use (sha1/sha256/md5)
 * @param boolean $salt If true, automatically appends the application's salt
 *     value to $string (Security.salt)
 * @return string Hash
 */
	public function hash($string, $type = null, $salt = false) {
		return Security::hash($string, $type, $salt);
	}

/**
 * Custom validation method to ensure that the two entered passwords match
 *
 * @param string $password Password
 * @return boolean Success
 */
	public function confirmPassword($password = null) {
		if ((isset($this->data[$this->alias]['password']) && isset($password['temppassword']))
			&& !empty($password['temppassword'])
			&& ($this->data[$this->alias]['password'] === $password['temppassword'])) {
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
 * Verifies a users email by a token that was sent to him via email and flags the user record as active
 *
 * @param string $token The token that wa sent to the user
 * @return array On success it returns the user data record
 */
	public function verifyEmail($token = null) {
		$user = $this->find('first', array(
			'contain' => array(),
			'conditions' => array(
				$this->alias . '.email_verified' => 0,
				$this->alias . '.email_token' => $token),
			'fields' => array(
				'id', 'email', 'email_token_expires', 'role')));

		if (empty($user)) {
			throw new RuntimeException(__d('users', 'Invalid token, please check the email you were sent, and retry the verification link.'));
		}

		$expires = strtotime($user[$this->alias]['email_token_expires']);
		if ($expires < time()) {
			throw new RuntimeException(__d('users', 'The token has expired.'));
		}

		$data[$this->alias]['active'] = 1;
		$user[$this->alias]['email_verified'] = 1;
		$user[$this->alias]['email_token'] = null;
		$user[$this->alias]['email_token_expires'] = null;

		$user = $this->save($user, array(
			'validate' => false,
			'callbacks' => false));
		$this->data = $user;
		return $user;
	}

/**
 * Validates the user token
 *
 * @deprecated See verifyEmail()
 * @param string $token Token
 * @param boolean $reset Reset boolean
 * @param boolean $now time() value
 * @return mixed false or user data
 */
	public function validateToken($token = null, $reset = false, $now = null) {
		if (!$now) {
			$now = time();
		}

		$data = false;
		$match = $this->find('first', array(
			'contain' => array(),
			'conditions' => array(
				$this->alias . '.email_token' => $token),
			'fields' => array(
				'id', 'email', 'email_token_expires', 'role')));

		if (!empty($match)) {
			$expires = strtotime($match[$this->alias]['email_token_expires']);
			if ($expires > $now) {
				$data[$this->alias]['id'] = $match[$this->alias]['id'];
				$data[$this->alias]['email'] = $match[$this->alias]['email'];
				$data[$this->alias]['email_verified'] = '1';
				$data[$this->alias]['role'] = $match[$this->alias]['role'];

				if ($reset === true) {
					$newPassword = $this->generatePassword();
					$data[$this->alias]['password'] = $this->hash($newPassword, null, true);
					$data[$this->alias]['new_password'] = $newPassword;
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
 * @param string $field Default is "last_action", changing it allows you to use this method also for "last_login" for example
 * @return boolean True on success
 */
	public function updateLastActivity($userId = null, $field = 'last_action') {
		if (!empty($userId)) {
			$this->id = $userId;
		}
		if ($this->exists()) {
			return $this->saveField($field, date('Y-m-d H:i:s', time()));
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
		$this->recursive = -1;
		$user = $this->find('first', array(
			'conditions' => array(
				$this->alias . '.active' => 1,
				$this->alias . '.email' => $postData[$this->alias]['email'])));

		if (!empty($user) && $user[$this->alias]['email_verified'] == 1) {
			$sixtyMins = time() + 43000;
			$token = $this->generateToken();
			$user[$this->alias]['password_token'] = $token;
			$user[$this->alias]['email_token_expires'] = date('Y-m-d H:i:s', $sixtyMins);
			$user = $this->save($user, false);
			$this->data = $user;
			return $user;
		} elseif (!empty($user) && $user[$this->alias]['email_verified'] == 0){
			$this->invalidate('email', __d('users', 'This Email Address exists but was never validated.'));
		} else {
			$this->invalidate('email', __d('users', 'This Email Address does not exist in the system.'));
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
			'new_password' => $tmp['password'],
			'confirm_password' => array(
				'required' => array(
					'rule' => array('compareFields', 'new_password', 'confirm_password'), 
					'message' => __d('users', 'The passwords are not equal.'))));

		$this->set($postData);
		if ($this->validates()) {
			$this->data[$this->alias]['password'] = $this->hash($this->data[$this->alias]['new_password'], null, true);
			$this->data[$this->alias]['password_token'] = null;
			$result = $this->save($this->data, array(
				'validate' => false,
				'callbacks' => false));
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
		$this->validate = $this->validatePasswordChange;

		$this->set($postData);
		if ($this->validates()) {
			$this->data[$this->alias]['password'] = $this->hash($this->data[$this->alias]['new_password'], null, true);
			$this->save($postData, array(
				'validate' => false,
				'callbacks' => false));
			return true;
		}
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
				throw new OutOfBoundsException(__d('users', '$this->data[\'' . $this->alias . '\'][\'id\'] has to be set and not empty'));
			}
		}

		$currentPassword = $this->field('password', array($this->alias . '.id' => $this->data[$this->alias]['id']));
		return $currentPassword === $this->hash($password['old_password'], null, true);
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
 * @param string $slug user slug or the uuid of a user
 * @return array
 */
	public function view($slug = null) {
		$user = $this->find('first', array(
			'contain' => array(
				'UserDetail'),
			'conditions' => array(
				'OR' => array(
					$this->alias . '.slug' => $slug,
					$this->alias . '.' . $this->primaryKey => $slug),
				$this->alias . '.active' => 1,
				$this->alias . '.email_verified' => 1)));

		if (empty($user)) {
			throw new OutOfBoundsException(__d('users', 'The user does not exist.'));
		}

		return $user;
	}

/**
 * Registers a new user
 *
 * Options:
 * - bool emailVerification : Default is true, generates the token for email verification
 * - bool removeExpiredRegistrations : Default is true, removes expired registrations to do cleanup when no cron is configured for that
 * - bool returnData : Default is true, if false the method returns true/false the data is always available through $this->User->data
 *
 * @param array $postData Post data from controller
 * @param mixed should be array now but can be boolean for emailVerification because of backward compatibility
 * @return mixed
 */
	public function register($postData = array(), $options = array()) {
		if (is_bool($options)) {
			$options = array('emailVerification' => $options);
		}

		$defaults = array(
			'emailVerification' => true,
			'removeExpiredRegistrations' => true,
			'returnData' => true);
		extract(array_merge($defaults, $options));

		$postData = $this->_beforeRegistration($postData, $emailVerification);

		if ($removeExpiredRegistrations) {
			$this->_removeExpiredRegistrations();
		}

		$this->set($postData);
		if ($this->validates()) {
			$postData[$this->alias]['password'] = $this->hash($postData[$this->alias]['password'], 'sha1', true);
			$this->create();
			$this->data = $this->save($postData, false);
			$this->data[$this->alias]['id'] = $this->id;
			if ($returnData) {
				return $this->data;
			}
			return true;
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
			$this->invalidate('email', __d('users', 'Please enter your email address.'));
			return false;
		}

		$user = $this->find('first', array(
			'contain' => array(),
			'conditions' => array(
				$this->alias . '.email' => $postData[$this->alias]['email'])));

		if (empty($user)) {
			$this->invalidate('email', __d('users', 'The email address does not exist in the system'));
			return false;
		}

		if ($user[$this->alias]['email_verified'] == 1) {
			$this->invalidate('email', __d('users', 'Your account is already authenticaed.'));
			return false;
		}

		if ($user[$this->alias]['active'] == 0) {
			$this->invalidate('email', __d('users', 'Your account is disabled.'));
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
 * Optional data manipulation before the registration record is saved
 *
 * @param array post data array
 * @param boolean Use email generation, create token, default true
 * @return array
 */
	protected function _beforeRegistration($postData = array(), $useEmailVerification = true) {
		if ($useEmailVerification == true) {
			$postData[$this->alias]['email_token'] = $this->generateToken();
			$postData[$this->alias]['email_token_expires'] = date('Y-m-d H:i:s', time() + 86400);
		} else {
			$postData[$this->alias]['email_verified'] = 1;
		}
        $postData[$this->alias]['active'] = 1;
        $defaultRole = Configure::read('Users.defaultRole');
        if ($defaultRole) {
            $postData[$this->alias]['role'] = $defaultRole;
        } else {
            $postData[$this->alias]['role'] = 'registered';
        }
        return $postData;
	}

/**
 * Returns the search data - Requires the CakeDC Search plugin to work
 *
 * @param string $state Find State
 * @param string $query Query options
 * @param string $results Result data
 * @return array
 * @link https://github.com/CakeDC/search
 */
	protected function _findSearch($state, $query, $results = array()) {
		if (!App::import('Lib', 'Utils.Languages')) {
			throw new MissingPluginException(array('plugin' => 'Search'));
		}

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
		if (isset($extra['type']) && isset($this->findMethods[$extra['type']])) {
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
 * @return boolean True if the data was saved successfully.
 */
	public function add($postData = null) {
		if (!empty($postData)) {
            $this->data = $postData;
            if ($this->validates()) {
                if (empty($postData[$this->alias]['role'])) {
                    if (empty($postData[$this->alias]['is_admin'])) {
                        $defaultRole = Configure::read('Users.defaultRole');
                        if ($defaultRole) {
                            $postData[$this->alias]['role'] = $defaultRole;
                        } else {
                            $postData[$this->alias]['role'] = 'registered';
                        }
                    } else {
                        $postData[$this->alias]['role'] = 'admin';
                    }
                }
                $postData[$this->alias]['password'] = $this->hash($postData[$this->alias]['password'], 'sha1', true);
                $this->create();
                $result = $this->save($postData, false);
                if ($result) {
                    $result[$this->alias][$this->primaryKey] = $this->id;
                    $this->data = $result;
                    return true;
                }
            }
		}
		return false;
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
				'UserDetail'),
			'conditions' => array($this->alias . '.id' => $userId)));

		$this->set($user);
		if (empty($user)) {
			throw new OutOfBoundsException(__d('users', 'Invalid User'));
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
			$this->alias . '.email_verified' => 0,
			$this->alias . '.email_token_expires <' => date('Y-m-d H:i:s')));
	}
}
