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
 * Users App Controller
 *
 * @package users
 * @subpackage users.controllers
 */

App::uses('AppController', 'Controller');

class UsersAppController extends AppController {

/**
 * Default isAuthorized method
 *
 * This is called to see if a user (when logged in) is able to access an action
 *
 * @return boolean True if allowed
 */
	public function isAuthorized() {
		return parent::isAuthorized();
	}

}
