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

if (!empty($details)) {
	echo $this->Form->create('Detail');
	foreach ($details as $detail) {
		$options = array();
		$options['type'] = $detail['Detail']['input'];
		if ($detail['Detail']['input'] == 'checkbox') {
			if ($detail['Detail']['value'] == 1) {
				$options['checked'] = true;
			}
		}
		if ($detail['Detail']['input'] == 'text' || $detail['Detail']['input'] == 'textarea' ) {
			$options['value'] = $detail['Detail']['value'];;
		}
		echo $this->Form->input($detail['Detail']['field'], ($options));
	}
	echo $this->Form->end(__d('users', 'Submit', true));
}
