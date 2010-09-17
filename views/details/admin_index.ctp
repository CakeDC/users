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
<div class="details index">
<h2><?php __d('users', 'Details');?></h2>
<p>
<?php
echo $paginator->counter(array(
'format' => __d('users', 'Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
));
?></p>
<table cellpadding="0" cellspacing="0">
<tr>
	<th><?php echo $paginator->sort('id');?></th>
	<th><?php echo $paginator->sort('user_id');?></th>
	<th><?php echo $paginator->sort('position');?></th>
	<th><?php echo $paginator->sort('field');?></th>
	<th><?php echo $paginator->sort('value');?></th>
	<th><?php echo $paginator->sort('created');?></th>
	<th><?php echo $paginator->sort('modified');?></th>
	<th class="actions"><?php __d('users', 'Actions');?></th>
</tr>
<?php
$i = 0;
foreach ($details as $detail):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $detail['Detail']['id']; ?>
		</td>
		<td>
			<?php echo $this->Html->link($detail['User']['id'], array('controller'=> 'users', 'action'=>'view', $detail['User']['id'])); ?>
		</td>
		<td>
			<?php echo $detail['Detail']['position']; ?>
		</td>
		<td>
			<?php echo $detail['Detail']['field']; ?>
		</td>
		<td>
			<?php echo $detail['Detail']['value']; ?>
		</td>
		<td>
			<?php echo $detail['Detail']['created']; ?>
		</td>
		<td>
			<?php echo $detail['Detail']['modified']; ?>
		</td>
		<td class="actions">
			<?php echo $this->Html->link(__d('users', 'View', true), array('action'=>'view', $detail['Detail']['id'])); ?>
			<?php echo $this->Html->link(__d('users', 'Edit', true), array('action'=>'edit', $detail['Detail']['id'])); ?>
			<?php echo $this->Html->link(__d('users', 'Delete', true), array('action'=>'delete', $detail['Detail']['id']), null, sprintf(__d('users', 'Are you sure you want to delete # %s?', true), $detail['Detail']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<div class="paging">
	<?php echo $paginator->prev('<< '.__d('users', 'previous', true), array(), null, array('class'=>'disabled'));?>
 | 	<?php echo $paginator->numbers();?>
	<?php echo $paginator->next(__d('users', 'next', true).' >>', array(), null, array('class'=>'disabled'));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__d('users', 'New Detail', true), array('action'=>'add')); ?></li>
		<li><?php echo $this->Html->link(__d('users', 'List Users', true), array('controller'=> 'users', 'action'=>'index')); ?> </li>
		<li><?php echo $this->Html->link(__d('users', 'New User', true), array('controller'=> 'users', 'action'=>'add')); ?> </li>
		<li><?php echo $this->Html->link(__d('users', 'List Groups', true), array('controller'=> 'groups', 'action'=>'index')); ?> </li>
		<li><?php echo $this->Html->link(__d('users', 'New Group', true), array('controller'=> 'groups', 'action'=>'add')); ?> </li>
	</ul>
</div>
