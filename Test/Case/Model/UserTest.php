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
 * UserTestCase
 *
 * @package users
 * @subpackage users.tests.cases.models
 */
class UserTestCase extends CakeTestCase {

/**
 * User model instance
 *
 * @var mixed
 */
	public $User = null;

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'Users';

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.users.user',
		'plugin.users.user_detail');

/**
 * startTest
 *
 * @return void
 */
	public function setUp() {
		Configure::write('App.UserClass', null);
		$this->User = ClassRegistry::init('Users.User');
	}

/**
 * endTest
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->User);
		ClassRegistry::flush(); 
	}
/**
 * 
 *
 * @return void
 */
	public function testUserInstance() {
		$this->assertTrue(is_a($this->User, 'User'));
	}

/**
 * Test to compare the passwords when a user adds
 *
 * @return void
 */
	public function testConfirmPassword() {
		$this->User->data['User']['password'] = 'password';
		$result = $this->User->confirmPassword(array('temppassword' => 'password'));
		$this->assertTrue($result);

		$this->User->data['User']['password'] = 'different_password';
		$result = $this->User->confirmPassword(array('temppassword' => 'password'));
		$this->assertFalse($result);
	}

/**
 * testValidateEmailConfirmation
 *
 * @return void
 */
	public function testConfirmEmail() {
		$this->User->data['User'] = array(
			'email' => 'test@email.com');
		$this->assertFalse($this->User->confirmEmail(array('confirm_email' => 'test@wrong.com')));

		$this->User->data['User'] = array(
			'email' => 'test@email.com');
		$this->assertTrue($this->User->confirmEmail(array('confirm_email' => 'test@email.com')));
	}

/**
 * Test if the generated token is a string
 *
 * @return void
 */
	function testGenerateToken() {
		$result = $this->User->generateToken();
		$this->assertInternalType('string', $result);
	}

/**
 * testValidateToken
 *
 * @return void
 */
	function testValidateToken() {
		$result = $this->User->validateToken('no valid token');
		$this->assertFalse($result);

		$now = strtotime('2008-03-25 02:48:46');
		$result = $this->User->validateToken('testtoken2', false, $now);
		$this->assertInternalType('array', $result);

		$now = strtotime('2008-03-29 02:48:46');
		$result = $this->User->validateToken('testtoken2', false, $now);
		$this->assertFalse($result);
	}

/**
 * testUpdateLastActivity
 *
 * @return void
 */
	public function testUpdateLastActivity() {
		$id = 1;
		$this->User->id = $id;
		$lastDate = $this->User->field('last_action');
		$result = $this->User->updateLastActivity($id);
		$this->assertTrue(is_array($result));
		$this->User->id = $id;
		$newDate = $result['User']['last_action'];
		$this->assertTrue($lastDate < $newDate);
		$this->assertFalse($this->User->updateLastActivity('invalid-id!'));
	}

/**
 * testResetPassword
 *
 * @return void
 */
	public function testResetPassword() {
		$data = array(
			'User' => array(
				'id' => 1,
				'new_password' => '',
				'confirm_password' => 'dsgdsgsdg'));
		$this->assertFalse($this->User->resetPassword($data));

		$data = array(
			'User' => array(
				'id' => 1,
				'new_password' => '',
				'confirm_password' => ''));
		$this->assertFalse($this->User->resetPassword($data));

		$data = array(
			'User' => array(
				'id' => 1,
				'new_password' => 'newpassword',
				'confirm_password' => 'newpassword'));
		$this->assertInternalType('array', $this->User->resetPassword($data));
	}

/**
 * testCheckPasswordToken
 *
 * @return void
 */
	public function testCheckPasswordToken() {
		$this->User->id = '1';
		$this->User->saveField('email_token_expires', date('Y-m-d H:i:s', strtotime('+1 year')));
		$this->assertInternalType('array', $this->User->checkPasswordToken('testtoken'));
		$this->assertFalse($this->User->checkPasswordToken('something-wrong-here'));
	}

/**
 * testPasswordReset
 *
 * @return void
 */
	public function testPasswordReset() {
		$data = array(
			'User' => array(
				'id' => 1,
				'email' => 'somethingwrong in here!'));
		$this->assertFalse($this->User->passwordReset($data));

		$this->User->id = '1';
		$this->User->saveField('email_token_expires', date('Y-m-d H:i:s', strtotime('+1 year')));
		$data = array(
			'User' => array(
				'id' => 1,
				'email' => 'adminuser@cakedc.com'));
		$this->assertInternalType('array', $this->User->passwordReset($data));
	}

/**
 * testValidateOldPassword
 *
 * @return void
 */
	public function testValidateOldPassword() {
		$password = $this->User->hash('password', null, true);
		$this->User->id = '1';
		$this->User->saveField('password', $password);
		$this->User->data = array(
			'User' => array(
				'id' => '1',
				'password'));

		$result = $this->User->validateOldPassword(array('old_password' => 'password'));
		$this->assertTrue($result);

		$result = $this->User->validateOldPassword(array('old_password' => 'FAIL!'));
		$this->assertFalse($result);
	}

/**
 * testView
 *
 * @return void
 */
	public function testView() {
		$result = $this->User->view('adminuser');
		$this->assertTrue(is_array($result) && !empty($result));

		$this->expectException('OutOfBoundsException');
		$result = $this->User->view('non-existing-user-slug');
	}

/**
 * Test the user registration method
 *
 * @return void
 */
	public function testRegister() {
		$postData = array();
		$result = $this->User->register($postData);
		$this->assertFalse($result);

		$postData = array('User' => array(
			'username' => '#236236326sdg!!!.s#invalid',
			'email' => 'invalid',
			'password' => 'password',
			'temppassword' => 'wrong',
			'tos' => 0));
		$result = $this->User->register($postData);
		$this->assertFalse($result);
		$this->assertEqual(array_keys($this->User->invalidFields()), array(
			'username', 'email', 'temppassword', 'tos'));

		$postData = array('User' => array(
			'username' => 'validusername',
			'email' => 'test@test.com',
			'password' => '12345',
			'temppassword' => '12345',
			'tos' => 1));
		$result = $this->User->register($postData);
		$this->assertFalse($result);
		$this->assertEqual(array_keys($this->User->invalidFields()), array(
			'password'));

		$postData = array('User' => array(
			'username' => 'imanewuser',
			'email' => 'foo@bar.com',
			'password' => 'password',
			'temppassword' => 'password',
			'tos' => 1));
		$result = $this->User->register($postData, array('returnData' => false));
		$this->assertTrue($result);
		$result = $this->User->data;

		$this->assertEqual($result['User']['active'], 1);
		$this->assertEqual($result['User']['password'], $this->User->hash('password', 'sha1', true));
		$this->assertTrue(is_string($result['User']['email_token']));

		$result = $this->User->findById($this->User->id);
		$this->assertEqual($result['User']['id'], $this->User->id);
	}

/**
 * testChangePassword
 *
 * @return void
 */
	public function testChangePassword() {
		$postData = array();
		$result = $this->User->changePassword($postData);
		$this->assertFalse($result);

		$postData = array(
			'User' => array(
				'id' => 1,
				'old_password' => 'test',
				'new_password' => 'not',
				'confirm_password' => 'equal'));

		$result = $this->User->changePassword($postData);
		$this->assertFalse($result);
		$this->assertEqual(array('new_password', 'confirm_password'), array_keys($this->User->invalidFields()));

		$postData = array(
			'User' => array(
				'id' => 1,
				'old_password' => 'test',
				'new_password' => 'testtest',
				'confirm_password' => 'testtest'));
		$result = $this->User->changePassword($postData);
		$this->assertTrue($result);
		$ressult = $this->User->find('first', array(
			'recursive' => -1,
			'conditions' => array(
				'User.id' => 1)));
		$this->assertEqual($ressult['User']['password'], $this->User->hash('testtest', null, true));
	}

/**
 * Test validation method to compare two fields
 *
 * @return void
 */
	public function testCompareFields() {
		$this->User->data = array(
			'User' => array(
				'field1' => 'foo',
				'field2' => 'bar'));
		$this->assertFalse($this->User->compareFields('field1', 'field2'));

		$this->User->data = array(
			'User' => array(
				'field1' => 'foo',
				'field2' => 'foo'));
		$this->assertTrue($this->User->compareFields('field1', 'field2'));
	}

/**
 * Test resending of the email authentication 
 *
 * @return void
 */
	public function testResendVerification() {
		$postData = array(
			'User' => array());
		$this->assertFalse($this->User->resendVerification($postData));

		$postData = array(
			'User' => array(
				'email' => 'doesnotexist!'));
		$this->assertFalse($this->User->resendVerification($postData));

		$postData = array(
			'User' => array(
				'email' => 'adminuser@cakedc.com'));
		$this->assertFalse($this->User->resendVerification($postData));

		$postData = array(
			'User' => array(
				'email' => 'oidtest2@testuser.com'));
		$result = $this->User->resendVerification($postData);
		$this->assertTrue(is_array($result));
	}

/**
 * Test resending of the email authentication 
 *
 * @return void
 */
	public function testGeneratePassword() {
		$result = $this->User->generatePassword();
		$this->assertInternalType('string', $result);
		$this->assertEqual(strlen($result), 10);

		$result = $this->User->generatePassword(15);
		$this->assertInternalType('string', $result);
		$this->assertEqual(strlen($result), 15);
	}

/**
 * testDelete
 *
 * @return void
 */
	public function testDelete() {
		$this->User->id = '1';
		$this->assertTrue($this->User->exists());
		$this->assertTrue($this->User->delete('1'));
		$this->assertFalse($this->User->exists());
	}

/**
 * testAdd
 *
 * @return void
 */
	public function testAdd() {
		$postData = array(
			'User' => array(
				'username' => 'newusername',
				'email' => 'newusername@newusername.com',
				'password' => 'password',
				'temppassword' => 'password',
				'tos' => 1));
		$result = $this->User->add($postData);
		$this->assertTrue($result);
	}

/**
 * testEdit
 *
 * @return void
 **/
	public function testEdit() {
		$userId = '1';
		$data = $this->User->read(null, $userId);
		$data['User']['email'] = 'anotherNewEmail@anothernewemail.com';

		$result = $this->User->edit(1, $data);
		$this->assertTrue($result);

		$result = $this->User->read(null, 1);
		$this->assertEqual($result['User']['username'], $data['User']['username']);
		$this->assertEqual($result['User']['email'], $data['User']['email']);
	}
	
/**
 * testEditException
 *
 * @return void
 */
	public function testEditException() {
		$this->setExpectedException('OutOfBoundsException');
		$userId = '1';
		$data = $this->User->read(null, $userId);
		$data['User']['email'] = 'anotherNewEmail@anothernewemail.com';
		$this->User->edit('bogus id', $userId, $data);
	}

/**
 * testDisableSlugs
 *
 * @return void
 */
	public function testDisableSlugs() {
		ClassRegistry::flush();
		$this->User = ClassRegistry::init('Users.User');
		$this->User->create();
		$this->User->save(array(
			'username' => 'foo2'), array('validate' => false));
		$result = $this->User->read(null, $this->User->id);
		$this->assertEquals($result['User']['slug'], 'foo2');

		ClassRegistry::flush();
		Configure::write('Users.disableSlugs', true);
		$this->User = ClassRegistry::init('Users.User');
		$this->User->create();
		$this->User->save(array(
			'username' => 'bar2'), array('validate' => false));
		$result = $this->User->read(null, $this->User->id);
		$this->assertTrue(empty($result['User']['slug']));
	}

}
