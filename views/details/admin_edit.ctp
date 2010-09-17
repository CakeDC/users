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
<div class="details form">
<?php echo $this->Form->create('Detail');?>
	<fieldset>
 		<legend><?php __d('users', 'Edit Detail');?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('user_id');
		echo $this->Form->input('position');
		echo $this->Form->input('field');
		echo $this->Form->input('value');
		echo $this->Form->input('Group');
	?>
	</fieldset>
<?php echo $this->Form->end('Submit');?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__d('users', 'Delete', true), array('action'=>'delete', $this->Form->value('Detail.id')), null, sprintf(__d('users', 'Are you sure you want to delete # %s?', true), $this->Form->value('Detail.id'))); ?></li>
		<li><?php echo $this->Html->link(__d('users', 'List Details', true), array('action'=>'index'));?></li>
		<li><?php echo $this->Html->link(__d('users', 'List Users', true), array('controller'=> 'users', 'action'=>'index')); ?> </li>
		<li><?php echo $this->Html->link(__d('users', 'New User', true), array('controller'=> 'users', 'action'=>'add')); ?> </li>
		<li><?php echo $this->Html->link(__d('users', 'List Groups', true), array('controller'=> 'groups', 'action'=>'index')); ?> </li>
		<li><?php echo $this->Html->link(__d('users', 'New Group', true), array('controller'=> 'groups', 'action'=>'add')); ?> </li>
	</ul>
</div>
