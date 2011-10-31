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
<h2><?php echo __d('users', 'Login'); ?></h2>
<fieldset>
	<legend><?php echo __d('users', 'Login'); ?></legend>
	<?php
	echo $this->Form->create($model, array('action' => 'login'));
		echo $this->Form->input('email', array('label' => __d('users', 'Email')));
		echo $this->Form->input('password',  array('label' => __d('users', 'Password')));
		echo __d('users', 'Remember Me') . $this->Form->checkbox('remember_me');
		echo $this->Form->hidden('User.return_to', array('value' => $return_to));
	echo $this->Form->end(__d('users', 'Submit'));
	?>
</fieldset>