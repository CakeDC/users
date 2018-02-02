<div class="actions">
	<ul>
		<?php if (!$this->Session->read('Auth.User.id')) : ?>
			<li><?php echo $this->Html->link(__d('users', 'Login'), ['plugin' => 'users', 'controller' => 'users', 'action' => 'login']); ?></li>
				<?php if (!empty($allowRegistration) && $allowRegistration) : ?>
			<li><?php echo $this->Html->link(__d('users', 'Register an account'), ['plugin' => 'users', 'controller' => 'users', 'action' => 'add']); ?></li>
		<?php endif; ?>
		<?php else : ?>
			<li><?php echo $this->Html->link(__d('users', 'Logout'), ['plugin' => 'users', 'controller' => 'users', 'action' => 'logout']); ?>
			<li><?php echo $this->Html->link(__d('users', 'My Account'), ['plugin' => 'users', 'controller' => 'users', 'action' => 'edit']); ?>
			<li><?php echo $this->Html->link(__d('users', 'Change password'), ['plugin' => 'users', 'controller' => 'users', 'action' => 'change_password']); ?>
		<?php endif ?>
		<?php if($this->Session->read('Auth.User.is_admin')) : ?>
			<li>&nbsp;</li>
			<li><?php echo $this->Html->link(__d('users', 'List Users'), ['plugin' => 'users', 'controller' => 'users', 'action' => 'index']);?></li>
		<?php endif; ?>
	</ul>
</div>
