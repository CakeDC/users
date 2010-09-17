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
<div class="users groups">
	<h2><?php __d('users', 'My Groups'); ?></h2>

	<ul>
		<li><?php echo $this->Html->link(__d('users', 'Create a new group', true), array('controller' => 'groups', 'action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__d('users', 'Invite a user', true), array('controller' => 'groups_user', 'action' => 'invite')); ?></li>
		<li><?php echo $this->Html->link(__d('users', 'Requests to join', true), array('controller' => 'groups_user', 'action' => 'index')); ?></li>
	</ul>

	<h3><?php __d('users', 'My own groups'); ?></h3>
	<table cellpadding="0" cellspacing="0">
		<tr>
			<th><?php __d('users', 'Name'); ?></th>
			<th><?php __d('users', 'Members'); ?></th>
			<th class="actions"><?php __d('users', 'Actions');?></th>
		</tr>
		<?php
		$i = 0;
		foreach ($user['Group'] as $group):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td>
				<?php echo $this->Html->link($group['name'], array('controller' => 'groups', 'action' => 'view', $group['id'])); ?>
			</td>
			<td>
				<?php echo $group['members_count']; ?>
			</td>
			<td class="actions">
				<?php echo $this->Html->link(__d('users', 'Edit', true), array('controller' => 'groups', 'action'=>'edit', $group['id'])); ?>
				<?php echo $this->Html->link(__d('users', 'Invite user', true), array('controller' => 'groups_user', 'action'=>'invite')); ?>
				<?php echo $this->Html->link(__d('users', 'Manage Broadcast Scope', true), array('controller' => 'broadcast_events', 'action' => 'scope', $group['id'])); ?>
				<?php echo $this->Html->link(__d('users', 'Access', true), array('controller' => 'groups', 'action'=>'access', $group['id'])); ?>
				<?php echo $this->Html->link(__d('users', 'Addons', true), array('controller' => 'groups', 'action'=>'assign_addon', $group['id'])); ?>
				<?php echo $this->Html->link(__d('users', 'Delete', true), array('controller' => 'groups', 'action'=>'delete', $group['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
	
	<h3><?php __d('users', 'Groups im a member in'); ?></h3>
	<table cellpadding="0" cellspacing="0">
		<tr>
			<th><?php __d('users', 'Name'); ?></th>
			<th><?php __d('users', 'Description'); ?></th>
			<th><?php __d('users', 'Members'); ?></th>
			<th class="actions"><?php __d('users', 'Actions');?></th>
		</tr>
		<?php
		$i = 0;
		foreach ($user['UserGroup'] as $group):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td>
				<?php echo $this->Html->link($group['name'], array('controller' => 'groups', 'action' => 'view', $group['id'])); ?>
			</td>
			<td>
				<?php echo $group['description']; ?>
			</td>
			<td>
				<?php echo $this->Html->link(__d('users', 'Leave group', true), array('controller' => 'groups_user', 'action' => 'leave', $group['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
</div>