<?php
/**
 * Copyright 2010 - 2014, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2014, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('AppModel', 'Model');

/**
 * Users App Model
 *
 * @package users
 * @subpackage users.models
 */
class UsersAppModel extends AppModel {

/**
 * Plugin name
 *
 * @var string $plugin
 */
	public $plugin = 'Users';

/**
 * Recursive level for finds
 *
 * @var integer
 */
	public $recursive = -1;

/**
 * Behaviors
 *
 * @var array
 */
	public $actsAs = array(
		'Containable'
	);

/**
 * Customized paginateCount method
 *
 * @param array
 * @param integer
 * @param array
 * @return mixed
 */
	public function paginateCount($conditions = array(), $recursive = 0, $extra = array()) {
		$parameters = compact('conditions');
		if ($recursive != $this->recursive) {
			$parameters['recursive'] = $recursive;
		}
		if (isset($extra['type']) && isset($this->findMethods[$extra['type']])) {
			$extra['operation'] = 'count';
			return $this->find($extra['type'], array_merge($parameters, $extra));
		}
		return $this->find('count', array_merge($parameters, $extra));
	}

}
