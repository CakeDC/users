<?php

/**
 * Users CakePHP Plugin
 *
 * Copyright 2009 - 2018, Cake Development Corporation
 *                 1785 E. Sahara Avenue, Suite 490-423
 *                 Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @Copyright 2009 - 2018, Cake Development Corporation
 * @link      http://github.com/CakeDC/users
 * @package   plugins.users.config.schema
 * @license   MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class UsersSchema extends CakeSchema {

	public $name = 'users';

/**
 * Before callback
 *
 * @param array $event Event
 * @return bool
 */
	public function before($event = []) {
		return true;
	}

/**
 * After callback
 *
 * @param array $event Event
 * @return void
 */
	public function after($event = []) {
	}

	public $users = [
		'id' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary'],
		'username' => ['type' => 'string', 'null' => false, 'default' => null, 'key' => 'index'],
		'slug' => ['type' => 'string', 'null' => false, 'default' => null],
		'password' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 128],
		'password_token' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 128],
		'email' => ['type' => 'string', 'null' => true, 'default' => null, 'key' => 'index'],
		'email_verified' => ['type' => 'boolean', 'null' => true, 'default' => '0'],
		'email_token' => ['type' => 'string', 'null' => true, 'default' => null],
		'email_token_expires' => ['type' => 'datetime', 'null' => true, 'default' => null],
		'tos' => ['type' => 'boolean', 'null' => true, 'default' => '0'],
		'active' => ['type' => 'boolean', 'null' => true, 'default' => '0'],
		'last_login' => ['type' => 'datetime', 'null' => true, 'default' => null],
		'last_action' => ['type' => 'datetime', 'null' => true, 'default' => null],
		'is_admin' => ['type' => 'boolean', 'null' => true, 'default' => '0'],
		'role' => ['type' => 'string', 'null' => true, 'default' => null],
		'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
		'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
		'indexes' => [
			'PRIMARY' => ['column' => 'id', 'unique' => 1],
			'BY_USERNAME' => ['column' => ['username'], 'unique' => 0],
			'BY_EMAIL' => ['column' => ['email'], 'unique' => 0]
		]
	];
}
