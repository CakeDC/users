<?php
class AllTagsPluginTest extends PHPUnit_Framework_TestSuite {

/**
 * Suite define the tests for this suite
 *
 * @return void
 */
	public static function suite() {
		$suite = new PHPUnit_Framework_TestSuite('All Users Plugin Tests');

		$basePath = CakePlugin::path('Users') . DS . 'Test' . DS . 'Case' . DS;
		// controllers
		$suite->addTestFile($basePath . 'Controller' . DS . 'UserDetailsControllerTest.php');
		$suite->addTestFile($basePath . 'Controller' . DS . 'UsersControllerTest.php');

		// models
		$suite->addTestFile($basePath . 'Model' . DS . 'UserDetailTest.php');
		$suite->addTestFile($basePath . 'Model' . DS . 'UserTest.php');

		return $suite;
	}

}