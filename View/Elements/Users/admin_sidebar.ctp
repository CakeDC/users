<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__d('users', 'Logout'), ['admin' => false, 'action' => 'logout']); ?>
		<li><?php echo $this->Html->link(__d('users', 'My Account'), ['admin' => false, 'action' => 'edit']); ?>
		<li>&nbsp;</li>
		<li><?php echo $this->Html->link(__d('users', 'Add Users'), ['admin' => true, 'action'=>'add']);?></li>
		<li><?php echo $this->Html->link(__d('users', 'List Users'), ['admin' => true, 'action'=>'index']);?></li>
		<li>&nbsp;</li>
		<li><?php echo $this->Html->link(__d('users', 'Frontend'), ['admin' => false, 'action'=>'index']); ?></li>
	</ul>
</div>