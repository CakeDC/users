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
?>
<h2><?php __d('users', 'Change your password'); ?></h2>
<p>
	<?php __d('users', 'Please enter your old password because of security reasons and then your new password twice.'); ?>
</p>
<?php
	echo $this->Form->create($model, array('action' => 'change_password'));
	echo $this->Form->input('old_password', array(
		'label' => __d('users', 'Old Password', true),
		'type' => 'password'));
	echo $this->Form->input('new_password', array(
		'label' => __d('users', 'New Password', true),
		'type' => 'password'));
	echo $this->Form->input('confirm_password', array(
		'label' => __d('users', 'Confirm', true),
		'type' => 'password'));
	echo $this->Form->end(__d('users', 'Submit', true));
?>