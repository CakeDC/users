<?php
/**
 * Copyright 2010 - 2013, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2013, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

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
		$suite->addTestFile($basePath . 'Controller' . DS . 'UsersControllerTest.php');

		// models
		$suite->addTestFile($basePath . 'Model' . DS . 'UserTest.php');

		return $suite;
	}

}