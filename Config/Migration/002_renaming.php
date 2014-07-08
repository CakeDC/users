<?php
/**
 * Users CakePHP Plugin
 *
 * Copyright 2010 - 2014, Cake Development Corporation
 *                 1785 E. Sahara Avenue, Suite 490-423
 *                 Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @Copyright 2010 - 2014, Cake Development Corporation
 * @link      http://github.com/CakeDC/users
 * @package   plugins.users.config.migrations
 * @license   MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class M4ef8ba03ff504ab2b415980575f6eb26 extends CakeMigration {
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
			'rename_field' => array(
				'users' => array(
					'email_token_expiry' => 'email_token_expires'
				),
			),
		),
		'down' => array(
			'rename_field' => array(
				'users' => array(
					'email_token_expires' => 'email_token_expiry'
				),
			),
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
