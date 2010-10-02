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

<div class="users form">
<?php echo $this->Form->create($model);?>
	<fieldset>
 		<legend><?php __d('users', 'Add User');?></legend>
	<?php
		
		echo $this->Form->input('username');
		echo $this->Form->input('passwd');
		echo $this->Form->input('temppassword',array('type'=>'password'));
		echo $this->Form->input('email');
		echo $this->Form->input('tos');
		echo $this->Form->input('active');
		echo $this->Form->input('is_admin');
		echo $this->Form->input('role');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__d('users', 'List Users', true), array('action'=>'index'));?></li>
	</ul>
</div>