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

App::import('Controller', 'Users.UserDetails');

/**
 * TestDetails
 *
 * @package users
 * @subpackage users.tests.controllers
 */
class TestUserDetails extends UserDetailsController {
	var $autoRender = false;
}

/**
 * DetailsController
 *
 * @package users
 * @author users.tests.controllers
 */
class UserDetailsControllerTest extends CakeTestCase {
	var $Details = null;

	function setUp() {
		Configure::write('App.UserClass', null);
		$this->UserDetails = new TestUserDetails();
	}

	function testDetailsControllerInstance() {
		$this->assertTrue(is_a($this->UserDetails, 'UserDetailsController'));
	}

	function tearDown() {
		unset($this->UserDetails);
	}
}
