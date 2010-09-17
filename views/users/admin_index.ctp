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

	<h3><?php __d('users', 'Filter'); ?></h3>
	<?php 
		echo $this->Form->create($model, array('action' => 'index'));
		echo $this->Form->input('username', array(
			'label' => __d('users', 'Username', true)));
		echo $this->Form->input('email', array(
			'label' => __d('users', 'Email', true)));
		echo $this->Form->end(__d('users', 'Search', true));
	?>

	<?php echo $this->element('paging'); ?>
	<table cellpadding="0" cellspacing="0">
		<tr>
			<th><?php echo $paginator->sort('username');?></th>
			<th><?php echo $paginator->sort('email');?></th>
			<th><?php echo $paginator->sort('created');?></th>
			<th><?php echo $paginator->sort('modified');?></th>
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
					<?php echo $user[$model]['email']; ?>
				</td>
				<td>
					<?php echo $user[$model]['created']; ?>
				</td>
				<td>
					<?php echo $user[$model]['modified']; ?>
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
