<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__d('users', 'Logout'), array('admin' => false, 'action' => 'logout')); ?>
		<li><?php echo $this->Html->link(__d('users', 'My Account'), array('admin' => false, 'action' => 'edit')); ?>
		<li>&nbsp;</li>
		<li><?php echo $this->Html->link(__d('users', 'Add Users'), array('admin' => true, 'action'=>'add'));?></li>
		<li><?php echo $this->Html->link(__d('users', 'List Users'), array('admin' => true, 'action'=>'index'));?></li>
		<li>&nbsp;</li>
		<li><?php echo $this->Html->link(__d('users', 'Frontend'), array('admin' => false, 'action'=>'index')); ?></li>
	</ul>
</div>