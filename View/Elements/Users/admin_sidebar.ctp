<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__d('users', 'Logout'), array('admin' => true, 'action' => 'logout')); ?>
		<li><?php echo $this->Html->link(__d('users', 'My Account'), array('admin' => true, 'action' => 'edit')); ?>
		<li>&nbsp;</li>
		<li><?php echo $this->Html->link(__d('users', 'Add Users', true), array('admin' => true, 'action'=>'add'));?></li>
		<li><?php echo $this->Html->link(__d('users', 'List Users', true), array('admin' => true, 'action'=>'index'));?></li>
		<li>&nbsp;</li>
		<li><?php echo $this->Html->link(__d('users', 'Frontend', true), array('action'=>'index')); ?></li>
	</ul>
</div>