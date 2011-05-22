<h2><?php __d('users', 'Reset your password'); ?></h2>

<?php
	echo $this->Form->create($model, array(
		'url' => array(
			'action' => 'reset_password',
			$token)));
	echo $this->Form->input('new_password', array(
		'label' => __d('users', 'New Password', true),
		'type' => 'password'));
	echo $this->Form->input('confirm_password', array(
		'label' => __d('users', 'Confirm', true),
		'type' => 'password'));
	echo $this->Form->submit(__d('users', 'Submit', true));
	echo $this->Form->end();
?>