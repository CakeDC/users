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
 * DetailTestCase
 *
 * @package users
 * @subpackage users.tests.cases.models
 */
class UserDetailTestCase extends CakeTestCase {

/**
 * Detail instance
 *
 * @var object
 */
	public $UserDetail = null;
	
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.users.user',
		'plugin.users.user_detail');

/**
 * start
 *
 * @return void
 */
	public function startTest() {
		Configure::write('App.UserClass', null);
		$this->UserDetail = ClassRegistry::init('Users.UserDetail');
	}

/**
 * testDetailInstance
 *
 * @return void
 */
	public function testDetailInstance() {
		$this->assertTrue(is_a($this->UserDetail, 'UserDetail'));
	}

/**
 * testDetailFind
 *
 * @return void
 */
	public function testUserDetailFind() {
		$this->UserDetail->recursive = -1;
		$results = $this->UserDetail->find('all');
		$this->assertTrue(!empty($results));
		$this->assertTrue(is_array($results));
	}

/**
 * testGetSection
 *
 * @return void
 */
	public function testGetSection() {
		$result = $this->UserDetail->getSection('47ea303a-3b2c-4251-b313-4816c0a800fa', 'User'); // phpnut
		$this->assertTrue(is_array($result));
		$this->assertTrue(!empty($result));
		$this->assertEqual($result, array(
			'User' => array(
				'firstname' => 'Larry',
				'middlename' => 'E',
				'lastname' => 'Masters')));


		$result = $this->UserDetail->getSection('47ea303a-3b2c-4251-b313-4816c0a800fa', 'Blog'); // phpnut
		$this->assertTrue(is_array($result));
		$this->assertTrue(!empty($result));
		$this->assertEqual($result, array(
			'Blog' => array(
				'name' => 'My blog')));


		$result = $this->UserDetail->getSection('47ea303a-3b2c-4251-b313-4816c0a800fa'); // phpnut
		$this->assertTrue(is_array($result));
		$this->assertTrue(!empty($result));
		$this->assertEqual($result, array(
			'User' => array(
				'firstname' => 'Larry',
				'middlename' => 'E',
				'lastname' => 'Masters'),
			'Blog' => array(
				'name' => 'My blog')));
	}

/**
 * testSaveSection
 *
 * @return void
 */
	public function testSaveSection() {
		$data = array(
			'UserDetail' => array(
				'biography' => 'Lipsum...',
				'firstname' => 'Florian',
				'lastname' => 'Krämer'));
		$this->UserDetail->saveSection('47ea303a-3cyc-k251-b313-4811c0a800bf', $data, 'User');
		$result = $this->UserDetail->getSection('47ea303a-3cyc-k251-b313-4811c0a800bf', 'User');
		$this->assertEqual($result, array(
			'User' => array(
				'biography' => 'Lipsum...',
				'firstname' => 'Florian',
				'lastname' => 'Krämer')));


		$data = array(
			'UserDetail' => array(
				'biography' => 'Lipsum...',
				'firstname' => 'Foo',
				'lastname' => 'Bar'));
		$this->UserDetail->saveSection('47ea303a-3cyc-k251-b313-4811c0a800bf', $data, 'User');
		$result = $this->UserDetail->getSection('47ea303a-3cyc-k251-b313-4811c0a800bf', 'User');
		$this->assertEqual($result, array(
			'User' => array(
				'biography' => 'Lipsum...',
				'firstname' => 'Foo',
				'lastname' => 'Bar')));


		$data = array(
			'User' => array(
				'email' => 'foo@bar.com'));
		$this->UserDetail->saveSection('47ea303a-3cyc-k251-b313-4811c0a800bf', $data, 'User');
		$result = $this->UserDetail->getSection('47ea303a-3cyc-k251-b313-4811c0a800bf', 'User');
		$this->UserDetail->User->id = '47ea303a-3cyc-k251-b313-4811c0a800bf';
		$result = $this->UserDetail->User->field('User.email');
		$this->assertEqual($result, 'foo@bar.com');
	}

}
