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
<?php echo $this->Form->create('UserDetail', array('action' => 'edit')); ?>
	<fieldset>
 		<legend><?php echo __d('users', 'Edit User Detail'); ?></legend>
	<?php
	echo $this->Form->input('firstname');
	echo $this->Form->input('middlename');
	echo $this->Form->input('lastname');
	echo $this->Form->input('biography');
	echo $this->Form->input('birthday');
	?>
	</fieldset>
<?php echo $this->Form->end('Submit'); ?>
</div>