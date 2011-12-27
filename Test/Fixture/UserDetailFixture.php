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

/**
 * User Detail Fixture
 *
 * @package users
 * @subpackage users.test.fixtures
 */
class UserDetailFixture extends CakeTestFixture {

/**
 * Name
 *
 * @var string $name
 */
	public $name = 'UserDetail';

/**
 * Table
 *
 * @var array $table
 */
	public $table = 'user_details';

/**
 * Fields
 *
 * @var array $fields
 */
	public $fields = array(
		'id' => array('type'=>'string', 'null' => false, 'length' => 36, 'key' => 'primary'),
		'user_id' => array('type'=>'string', 'null' => false, 'length' => 36),
		'position' => array('type'=>'float', 'null' => false, 'default' => '1', 'length' => 4),
		'field' => array('type'=>'string', 'null' => false, 'key' => 'index'),
		'value' => array('type'=>'text', 'null' => true, 'default' => NULL),
		'created' => array('type'=>'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type'=>'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'UNIQUE_PROFILE_PROPERTY' => array('column' => array('field', 'user_id'), 'unique' => 1)
		)
	);

/**
 * Records
 *
 * @var array $records
 */
	public $records = array(
		array(
			'id'  => '491d06d1-0648-407b-81f5-347182f0cb67',
			'user_id'  => '47ea303a-3b2c-4251-b313-4816c0a800fa', //phpnut
			'position'  => 2,
			'field'  => 'User.firstname',
			'value'  => 'Larry',
			'created'  => '2008-03-25 01:47:31',
			'modified'  => '2008-03-25 01:47:31'),
		array(
			'id'  => '491d06f0-b93c-43ba-9b79-346082f0cb67',
			'user_id'  => '47ea303a-3b2c-4251-b313-4816c0a800fa',//phpnut
			'position'  => 3,
			'field'  => 'User.middlename',
			'value'  => 'E',
			'created'  => '2008-03-25 01:47:31',
			'modified'  => '2008-03-25 01:47:31'),
		array(
			'id'  => '491d0704-5e68-4de2-92c7-345c82f0cb67',
			'user_id'  => '47ea303a-3b2c-4251-b313-4816c0a800fa',//phpnut
			'position'  => 4,
			'field'  => 'User.lastname',
			'value'  => 'Masters',
			'created'  => '2008-03-25 01:47:31',
			'modified'  => '2008-03-25 01:47:31'),
		array(
			'id'  => '491d0704-5e68-4de3-92c7-345c82f0cb67',
			'user_id'  => '47ea303a-3b2c-4251-b313-4816c0a800fa',//phpnut
			'position'  => 5,
			'field'  => 'Blog.name',
			'value'  => 'My blog',
			'created'  => '2008-03-25 01:47:31',
			'modified'  => '2008-03-25 01:47:31')
	);
}
