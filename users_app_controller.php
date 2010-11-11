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
 * Users App Controller
 *
 * @package users
 */
class UsersAppController extends AppController {

/**
 * Determine if the user is authorized to view the requested action
 *
 * Inspect the URL and return true if the user is authorized
 *
 * @return boolean Authorized to view action
 */
	public function isAuthorized() {
		$authorized = true;

		// Restrict "admin" prefix routes to users with the role "admin".
		if (isset($this->params['prefix']) && $this->params['prefix'] == 'admin') {
			$authorized = $this->Auth->user('role') === 'admin';
		}
		return $authorized;
	}
}
