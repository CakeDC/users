<?php
/**
 * Users CakePHP Plugin
 *
 * Copyright 2010 - 2011, Cake Development Corporation
 *                 1785 E. Sahara Avenue, Suite 490-423
 *                 Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @Copyright 2010 - 2011, Cake Development Corporation
 * @link      http://github.com/CakeDC/users
 * @package   plugins.users.config.migrations
 * @license   MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class InitializeUsersSchema extends CakeMigration {
/**
 * Dependency array. Define what minimum version required for other part of db schema
 *
 * Migration defined like 'app.31' or 'plugin.PluginName.12'
 *
 * @var array $dependendOf
 */
	public $dependendOf = array();
/**
 * Migration array
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'create_table' => array(
				'user_details' => array(
					'id' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary'),
					'user_id' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 36),
					'position' => array('type'=>'float', 'null' => false, 'default' => '1', 'length' => 4),
					'field' => array('type'=>'string', 'null' => false, 'default' => NULL, 'key' => 'index'),
					'value' => array('type'=>'text', 'null' => true, 'default' => NULL),
					'input' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 16),
					'data_type' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 16),
					'label' => array('type'=>'string', 'null' => false, 'default' => '', 'length' => 128),
					'created' => array('type'=>'datetime', 'null' => true, 'default' => NULL),
					'modified' => array('type'=>'datetime', 'null' => true, 'default' => NULL),
					'indexes' => array(
						'PRIMARY' => array('column' => 'id', 'unique' => 1), 
						'UNIQUE_PROFILE_PROPERTY' => array('column' => array('field', 'user_id'), 'unique' => 1))
				),
				'users' => array(
					'id' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary'),
					'username' => array('type'=>'string', 'null' => false, 'default' => NULL),
					'slug' => array('type'=>'string', 'null' => false, 'default' => NULL),
					'password' => array('type'=>'string', 'null' => true, 'default' => NULL, 'length' => 128),
					'password_token' => array('type'=>'string', 'null' => true, 'default' => NULL, 'length' => 128),
					'email' => array('type'=>'string', 'null' => true, 'default' => NULL),
					'email_verified' => array('type'=>'boolean', 'null' => true, 'default' => '0'),
					'email_token' => array('type'=>'string', 'null' => true, 'default' => NULL),
					'email_token_expiry' => array('type'=>'datetime', 'null' => true, 'default' => NULL),
					'tos' => array('type'=>'boolean', 'null' => true, 'default' => '0'),
					'active' => array('type'=>'boolean', 'null' => true, 'default' => '0'),
					'last_login' => array('type'=>'datetime', 'null' => true, 'default' => NULL),
					'last_action' => array('type'=>'datetime', 'null' => true, 'default' => NULL),
					'is_admin' => array('type'=>'boolean', 'null' => true, 'default' => '0'),
					'role' => array('type'=>'string', 'null' => true, 'default' => NULL),
					'created' => array('type'=>'datetime', 'null' => true, 'default' => NULL),
					'modified' => array('type'=>'datetime', 'null' => true, 'default' => NULL),
					'indexes' => array(
						'PRIMARY' => array('column' => 'id', 'unique' => 1),
						'BY_USERNAME' => array('column' => array('username'), 'unique' => 0),
						'BY_EMAIL' => array('column' => array('email'), 'unique' => 0)
					),
				),
			),
		),
		'down' => array(
			'drop_table' => array(
				'users', 'user_details'),
		)
	);

/**
 * before migration callback
 *
 * @param string $direction, up or down direction of migration process
 */
	public function before($direction) {
		return true;
	}

/**
 * after migration callback
 *
 * @param string $direction, up or down direction of migration process
 */
	public function after($direction) {
		return true;
	}

}
