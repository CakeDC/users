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
?>
<div class="users form">
<h2><?php echo __d('users', 'Change your password'); ?></h2>
<p><?php echo __d('users', 'Please enter your old password because of security reasons and then your new password twice.'); ?></p>
	<?php
		echo $this->Form->create($model, array('action' => 'change_password'));
		echo $this->Form->input('old_password', array(
			'label' => __d('users', 'Old Password'),
			'type' => 'password'));
		echo $this->Form->input('new_password', array(
			'label' => __d('users', 'New Password'),
			'type' => 'password'));
		echo $this->Form->input('confirm_password', array(
			'label' => __d('users', 'Confirm'),
			'type' => 'password'));
		echo $this->Form->end(__d('users', 'Submit'));
	?>
</div>
<?php echo $this->element('Users.Users/sidebar'); ?>