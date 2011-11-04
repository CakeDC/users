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

if (!empty($user_details)) {
	echo $this->Form->create('UserDetail');
	foreach ($user_details as $detail) {
		$options = array();
		$options['type'] = $detail['UserDetail']['input'];
		if ($detail['UserDetail']['input'] == 'checkbox') {
			if ($detail['UserDetail']['value'] == 1) {
				$options['checked'] = true;
			}
		}
		if ($detail['UserDetail']['input'] == 'text' || $detail['UserDetail']['input'] == 'textarea' ) {
			$options['value'] = $detail['UserDetail']['value'];;
		}
		echo $this->Form->input($detail['UserDetail']['field'], ($options));
	}
	echo $this->Form->end(__d('users', 'Submit'));
}
