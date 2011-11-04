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
?>
<div class="users form">
	<h2><?php echo __d('users', 'Add User'); ?></h2>
	<fieldset>
		<?php
			echo $this->Form->create($model);
			echo $this->Form->input('username', array(
				'label' => __d('users', 'Username')));
			echo $this->Form->input('email', array(
				'label' => __d('users', 'E-mail (used as login)',true),
				'error' => array('isValid' => __d('users', 'Must be a valid email address', true),
				'isUnique' => __d('users', 'An account with that email already exists', true))));
			echo $this->Form->input('password', array(
				'label' => __d('users', 'Password',true),
				'type' => 'password'));
			echo $this->Form->input('temppassword', array(
				'label' => __d('users', 'Password (confirm)', true),
				'type' => 'password'));
			$tosLink = $this->Html->link(__d('users', 'Terms of Service', true), array('controller' => 'pages', 'action' => 'tos'));
			echo $this->Form->input('tos', array(
				'label' => __d('users', 'I have read and agreed to ', true) . $tosLink));
			echo $this->Form->end(__d('users', 'Submit',true));
		?>
	</fieldset>
</div>
<?php echo $this->element('Users/sidebar'); ?>