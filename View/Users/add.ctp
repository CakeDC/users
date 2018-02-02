<?php
/**
 * Copyright 2009 - 2018, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2009 - 2018, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<div class="users form">
	<h2><?php echo __d('users', 'Add User'); ?></h2>
	<fieldset>
		<?php
			echo $this->Form->create($model);
			echo $this->Form->input('username', [
				'label' => __d('users', 'Username')]);
			echo $this->Form->input('email', [
				'label' => __d('users', 'E-mail (used as login)'),
				'error' => ['isValid' => __d('users', 'Must be a valid email address'),
				'isUnique' => __d('users', 'An account with that email already exists')]]);
			echo $this->Form->input('password', [
				'label' => __d('users', 'Password'),
				'type' => 'password']);
			echo $this->Form->input('temppassword', [
				'label' => __d('users', 'Password (confirm)'),
				'type' => 'password']);
			$tosLink = $this->Html->link(__d('users', 'Terms of Service'), ['controller' => 'pages', 'action' => 'tos', 'plugin' => null]);
			echo $this->Form->input('tos', [
				'label' => __d('users', 'I have read and agreed to ') . $tosLink]);
			echo $this->Form->end(__d('users', 'Submit'));
		?>
	</fieldset>
</div>
<?php echo $this->element('Users.Users/sidebar'); ?>
