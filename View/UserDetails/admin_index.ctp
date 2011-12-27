<?php
/**
 * Copyright 2010 - 2011, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2011, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<div class="user_details index">
<h2><?php echo __d('users', 'User Details');?></h2>
<p><?php
echo $this->Paginator->counter(array(
	'format' => __d('users', 'Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%')
));
?></p>
<table cellpadding="0" cellspacing="0">
<tr>
	<th><?php echo $this->Paginator->sort('id');?></th>
	<th><?php echo $this->Paginator->sort('user_id');?></th>
	<th><?php echo $this->Paginator->sort('position');?></th>
	<th><?php echo $this->Paginator->sort('field');?></th>
	<th><?php echo $this->Paginator->sort('value');?></th>
	<th><?php echo $this->Paginator->sort('created');?></th>
	<th><?php echo $this->Paginator->sort('modified');?></th>
	<th class="actions"><?php echo __d('users', 'Actions');?></th>
</tr>
<?php
$i = 0;
foreach ($user_details as $user_detail):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $user_detail['UserDetail']['id']; ?>
		</td>
		<td>
			<?php echo $this->Html->link($user_detail['User']['id'], array('controller' => 'users', 'action' => 'view', $user_detail['User']['id'])); ?>
		</td>
		<td>
			<?php echo $user_detail['UserDetail']['position']; ?>
		</td>
		<td>
			<?php echo $user_detail['UserDetail']['field']; ?>
		</td>
		<td>
			<?php echo $user_detail['UserDetail']['value']; ?>
		</td>
		<td>
			<?php echo $user_detail['UserDetail']['created']; ?>
		</td>
		<td>
			<?php echo $user_detail['UserDetail']['modified']; ?>
		</td>
		<td class="actions">
			<?php echo $this->Html->link(__d('users', 'View'), array('action'=>'view', $user_detail['UserDetail']['id'])); ?>
			<?php echo $this->Html->link(__d('users', 'Edit'), array('action'=>'edit', $user_detail['UserDetail']['id'])); ?>
			<?php echo $this->Html->link(__d('users', 'Delete'), array('action'=>'delete', $user_detail['UserDetail']['id']), null, sprintf(__d('users', 'Are you sure you want to delete # %s?'), $user_detail['UserDetail']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<?php echo $this->element('pagination'); ?>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__d('users', 'New User Detail'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__d('users', 'List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__d('users', 'New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
