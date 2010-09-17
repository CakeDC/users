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
<div class="details view">
<h2><?php  __d('users', 'Detail');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __d('users', 'Id'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $detail['Detail']['id']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __d('users', 'User'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($detail['User']['id'], array('controller'=> 'users', 'action'=>'view', $detail['User']['id'])); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __d('users', 'Position'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $detail['Detail']['position']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __d('users', 'Field'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $detail['Detail']['field']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __d('users', 'Value'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $detail['Detail']['value']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __d('users', 'Created'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $detail['Detail']['created']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __d('users', 'Modified'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $detail['Detail']['modified']; ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__d('users', 'Edit Detail', true), array('action'=>'edit', $detail['Detail']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__d('users', 'Delete Detail', true), array('action'=>'delete', $detail['Detail']['id']), null, sprintf(__d('users', 'Are you sure you want to delete # %s?', true), $detail['Detail']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__d('users', 'List Details', true), array('action'=>'index')); ?> </li>
		<li><?php echo $this->Html->link(__d('users', 'New Detail', true), array('action'=>'add')); ?> </li>
		<li><?php echo $this->Html->link(__d('users', 'List Users', true), array('controller'=> 'users', 'action'=>'index')); ?> </li>
		<li><?php echo $this->Html->link(__d('users', 'New User', true), array('controller'=> 'users', 'action'=>'add')); ?> </li>
		<li><?php echo $this->Html->link(__d('users', 'List Groups', true), array('controller'=> 'groups', 'action'=>'index')); ?> </li>
		<li><?php echo $this->Html->link(__d('users', 'New Group', true), array('controller'=> 'groups', 'action'=>'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php __d('users', 'Related Groups');?></h3>
	<?php if (!empty($detail['Group'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php __d('users', 'Id'); ?></th>
		<th><?php __d('users', 'User Id'); ?></th>
		<th><?php __d('users', 'Is Public'); ?></th>
		<th><?php __d('users', 'Name'); ?></th>
		<th><?php __d('users', 'Description'); ?></th>
		<th><?php __d('users', 'Created'); ?></th>
		<th><?php __d('users', 'Modified'); ?></th>
		<th class="actions"><?php __d('users', 'Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($detail['Group'] as $group):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $group['id'];?></td>
			<td><?php echo $group['user_id'];?></td>
			<td><?php echo $group['is_public'];?></td>
			<td><?php echo $group['name'];?></td>
			<td><?php echo $group['description'];?></td>
			<td><?php echo $group['created'];?></td>
			<td><?php echo $group['modified'];?></td>
			<td class="actions">
				<?php echo $this->Html->link(__d('users', 'View', true), array('controller'=> 'groups', 'action'=>'view', $group['id'])); ?>
				<?php echo $this->Html->link(__d('users', 'Edit', true), array('controller'=> 'groups', 'action'=>'edit', $group['id'])); ?>
				<?php echo $this->Html->link(__d('users', 'Delete', true), array('controller'=> 'groups', 'action'=>'delete', $group['id']), null, sprintf(__d('users', 'Are you sure you want to delete # %s?', true), $group['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__d('users', 'New Group', true), array('controller'=> 'groups', 'action'=>'add'));?> </li>
		</ul>
	</div>
</div>
