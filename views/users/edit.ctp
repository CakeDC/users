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
<?php echo $this->Form->create($model);?>
	<fieldset>
 		<legend><?php __d('users', 'Edit User');?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('account_type');
		echo $this->Form->input('url');
		echo $this->Form->input('username');
		echo $this->Form->input('BroadcastEvent');
		echo $this->Form->input('UserGroup');
		echo $this->Form->input('Addon');
	?>
	</fieldset>
<?php echo $this->Form->end('Submit');?>