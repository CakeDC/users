<div class="actions">
	<ul>
		<?php if (!$this->Session->check('Auth.User.id')) : ?>
			<li><?php echo $this->Html->link(__d('users', 'Login'), array('action' => 'login')); ?></li>
			<li><?php echo $this->Html->link(__d('users', 'Register an account'), array('action' => 'add')); ?></li>
		<?php else : ?>
			<li><?php echo $this->Html->link(__d('users', 'Logout'), array('action' => 'logout')); ?>
			<li><?php echo $this->Html->link(__d('users', 'My Account'), array('action' => 'edit')); ?>
		<?php endif ?>
		<li>&nbsp;</li>
		<li><?php echo $this->Html->link(__d('users', 'List Users'), array('action'=>'index'));?></li>
	</ul>
</div>