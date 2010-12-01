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
<div class="users view">
	<h2><?php echo sprintf(__d('users', 'Welcome %s', true), $user[$model]['username']); ?></h2>
	<?php if (isset($this->Gravatar)): ?>
		<div class="avatar">
			<?php echo $this->Gravatar->image($user[$model]['email']); ?>
		</div>
	<?php endif; ?>
</div>
<div class="actions">
	<h3><?php __d('users', 'Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__d('users', 'Logout', true), array('action' => 'logout')); ?></li>
	</ul>
</div>