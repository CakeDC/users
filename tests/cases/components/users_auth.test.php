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

class UsersAuthTestCase extends CakeTestCase {

/**
 * Test setting the cookie
 *
 */
	public function testSetCookie() {
		$this->Users->data['User'] = array(
			'remember_me' => 1,
			'username' => 'test',
			'password' => 'testtest');
		$this->Users->setCookie(array(
			'name' => 'userTestCookie'));
		$this->Users->Cookie->name = 'userTestCookie';
		$result = $this->Users->Cookie->read('User');
		$this->assertEqual($result, array(
			'username' => 'test',
			'password' => 'testtest'));
	}
}
