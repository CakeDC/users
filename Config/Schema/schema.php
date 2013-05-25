<?php 
/**
 * Users CakePHP Plugin
 *
 * Copyright 2010 - 2013, Cake Development Corporation
 *                 1785 E. Sahara Avenue, Suite 490-423
 *                 Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @Copyright 2010 - 2013, Cake Development Corporation
 * @link      http://github.com/CakeDC/users
 * @package   plugins.users.config.schema
 * @license   MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class usersSchema extends CakeSchema {
	var $name = 'users';

	function before($event = array()) {
		return true;
	}

	function after($event = array()) {
	}

	var $user_details = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary'),
		'user_id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36),
		'position' => array('type' => 'float', 'null' => false, 'default' => '1'),
		'field' => array('type' => 'string', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'value' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'input' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 16),
		'data_type' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 16),
		'label' => array('type' => 'string', 'null' => false, 'length' => 128),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'UNIQUE_PROFILE_PROPERTY' => array('column' => array('field', 'user_id'), 'unique' => 1))
	);
	var $users = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary'),
		'username' => array('type' => 'string', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'slug' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'password' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 128),
		'password_token' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 128),
		'email' => array('type' => 'string', 'null' => true, 'default' => NULL, 'key' => 'index'),
		'email_verified' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'email_token' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'email_token_expires' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'tos' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'active' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'last_login' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'last_action' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'is_admin' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'role' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'BY_USERNAME' => array('column' => array('username'), 'unique' => 0), 'BY_EMAIL' => array('column' => array('email'), 'unique' => 0))
	);
}
