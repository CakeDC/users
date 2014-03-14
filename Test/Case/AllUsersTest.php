<?php
/**
 * All Users plugin tests
 */
class AllUsersTest extends CakeTestCase {

/**
 * Suite define the tests for this plugin
 *
 * @return void
 */
	public static function suite() {
		$suite = new CakeTestSuite('All Users test');

		$path = CakePlugin::path('Users') . 'Test' . DS . 'Case' . DS;

		//$suite->addTestFile($path . 'Controller' . DS . 'UsersControllerTest.php');
		//$suite->addTestFile($path . 'Model' . DS . 'UserTest.php');
		$suite->addTestDirectoryRecursive($path);

		return $suite;
	}

}
