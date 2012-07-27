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
<div class="user_details form">
<?php echo $this->Form->create('UserDetail'); ?>
	<fieldset>
 		<legend><?php echo __d('users', 'Add User Detail'); ?></legend>
	<?php
		echo $this->Form->input('user_id');
		echo $this->Form->input('position');
		echo $this->Form->input('field');
		echo $this->Form->input('value');
	?>
	</fieldset>
<?php echo $this->Form->end(__d('users', 'Submit')); ?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__d('users', 'List Details'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__d('users', 'List Users'), array('controller' => 'users', 'action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__d('users', 'New User'), array('controller' => 'users', 'action' => 'add')); ?></li>
	</ul>
</div>
