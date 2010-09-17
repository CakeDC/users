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
 * DetailTestCase
 *
 * @package users
 * @subpackage users.tests.cases.models
 */
class DetailTestCase extends CakeTestCase {

/**
 * Detail instance
 *
 * @var object
 */
	public $Detail = null;
	
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.users.user',
		'plugin.users.detail',
		'plugin.users.identity');

/**
 * start
 *
 * @return void
 */
	public function start() {
		Configure::write('App.UserClass', null);
		parent::start();
		$this->Detail =& ClassRegistry::init('Users.Detail');
	}

/**
 * testDetailInstance
 *
 * @return void
 */
	public function testDetailInstance() {
		$this->assertTrue(is_a($this->Detail, 'Detail'));
	}

/**
 * testDetailFind
 *
 * @return void
 */
	public function testDetailFind() {
		$this->Detail->recursive = -1;
		$results = $this->Detail->find('all');
		$this->assertTrue(!empty($results));
		$this->assertTrue(is_array($results));
	}

/**
 * testGetSection
 *
 * @return void
 */
	public function testGetSection() {
		$result = $this->Detail->getSection('47ea303a-3b2c-4251-b313-4816c0a800fa', 'User'); // phpnut
		$this->assertTrue(is_array($result));
		$this->assertTrue(!empty($result));
		$this->assertEqual($result, array(
			'User' => array(
				'firstname' => 'Larry',
				'middlename' => 'E',
				'lastname' => 'Masters')));


		$result = $this->Detail->getSection('47ea303a-3b2c-4251-b313-4816c0a800fa', 'Blog'); // phpnut
		$this->assertTrue(is_array($result));
		$this->assertTrue(!empty($result));
		$this->assertEqual($result, array(
			'Blog' => array(
				'name' => 'My blog')));


		$result = $this->Detail->getSection('47ea303a-3b2c-4251-b313-4816c0a800fa'); // phpnut
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
			'Detail' => array(
				'biography' => 'Lipsum...',
				'firstname' => 'Florian',
				'lastname' => 'Krämer'));
		$this->Detail->saveSection('47ea303a-3cyc-k251-b313-4811c0a800bf', $data, 'User');
		$result = $this->Detail->getSection('47ea303a-3cyc-k251-b313-4811c0a800bf', 'User');
		$this->assertEqual($result, array(
			'User' => array(
				'biography' => 'Lipsum...',
				'firstname' => 'Florian',
				'lastname' => 'Krämer')));


		$data = array(
			'Detail' => array(
				'biography' => 'Lipsum...',
				'firstname' => 'Foo',
				'lastname' => 'Bar'));
		$this->Detail->saveSection('47ea303a-3cyc-k251-b313-4811c0a800bf', $data, 'User');
		$result = $this->Detail->getSection('47ea303a-3cyc-k251-b313-4811c0a800bf', 'User');
		$this->assertEqual($result, array(
			'User' => array(
				'biography' => 'Lipsum...',
				'firstname' => 'Foo',
				'lastname' => 'Bar')));


		$data = array(
			'User' => array(
				'email' => 'foo@bar.com'));
		$this->Detail->saveSection('47ea303a-3cyc-k251-b313-4811c0a800bf', $data, 'User');
		$this->Detail->User->id = '47ea303a-3cyc-k251-b313-4811c0a800bf';
		$result = $this->Detail->User->field('User.email');
		$this->assertEqual($result, 'foo@bar.com');
	}

}
