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
<div class="users index">
<h2><?php __d('users', 'Users');?></h2>
<p>
<?php
echo $this->Paginator->counter(array(
'format' => __d('users', 'Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
));
?></p>
<table cellpadding="0" cellspacing="0">
<tr>
	<th><?php echo $this->Paginator->sort('username');?></th>
	<th><?php echo $this->Paginator->sort('created');?></th>
	<th class="actions"><?php __d('users', 'Actions');?></th>
</tr>
<?php
$i = 0;
foreach ($users as $user):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $user[$model]['username']; ?>
		</td>
		<td>
			<?php echo $user[$model]['created']; ?>
		</td>
		<td class="actions">
			<?php echo $this->Html->link(__d('users', 'View', true), array('action'=>'view', $user[$model]['id'])); ?>
			<?php echo $this->Html->link(__d('users', 'Edit', true), array('action'=>'edit', $user[$model]['id'])); ?>
			<?php echo $this->Html->link(__d('users', 'Delete', true), array('action'=>'delete', $user[$model]['id']), null, sprintf(__d('users', 'Are you sure you want to delete # %s?', true), $user[$model]['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<div class="paging">
	<?php echo $this->Paginator->prev('<< '.__d('users', 'previous', true), array(), null, array('class'=>'disabled'));?>
 | 	<?php echo $this->Paginator->numbers();?>
	<?php echo $this->Paginator->next(__d('users', 'next', true).' >>', array(), null, array('class'=>'disabled'));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__d('users', 'Register an account', true), array('action' => 'register')); ?></li>
	</ul>
</div>
