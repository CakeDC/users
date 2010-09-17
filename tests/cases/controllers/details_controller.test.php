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

App::import('Controller', 'Users.Details');

/**
 * TestDetails
 *
 * @package users
 * @subpackage users.tests.controllers
 */
class TestDetails extends DetailsController {
	var $autoRender = false;
}

/**
 * DetailsController
 *
 * @package users
 * @author users.tests.controllers
 */
class DetailsControllerTest extends CakeTestCase {
	var $Details = null;

	function setUp() {
		Configure::write('App.UserClass', null);
		$this->Details = new TestDetails();
	}

	function testDetailsControllerInstance() {
		$this->assertTrue(is_a($this->Details, 'DetailsController'));
	}

	function tearDown() {
		unset($this->Details);
	}
}
