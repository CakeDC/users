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

if (!$this->Session->check('Auth.Users')) {
	echo $this->Form->create('User', array(
		'url' => array(
			'admin' => false,
			'plugin' => 'users',
			'controller' => 'users',
			'action' => 'login'),
		'id' => 'LoginForm'));
	echo $this->Form->input('email', array(
		'label' => __d('users', 'Email', true)));
	echo $this->Form->input('passwd', array(
		'label' => __d('users', 'Password', true),
		'type' => 'password'));
	echo $this->Form->end(__d('users', 'Login', true));
}
