<?php
/**
 * Copyright 2009 - 2018, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2009 - 2018, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<div class="users index">
	<h2><?php echo __d('users', 'Users'); ?></h2>
	<?php
		echo $this->Form->create($model, [
			'action' => 'search']);
		echo $this->Form->input('username', [
			'label' => __d('users', 'Username')]);
		echo $this->Form->input('email', [
			'label' => __d('users', 'Email')]);
		echo $this->Form->input('Profile.name', [
			'label' => __d('users', 'Name')]);
		echo $this->Form->end(__d('users', 'Search'));
	?>
	<p><?php
	echo $this->Paginator->counter([
		'format' => __d('users', 'Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%')
	]);
	?></p>

	<table cellpadding="0" cellspacing="0">
	<tr>
		<th><?php echo $this->Paginator->sort('username'); ?></th>
		<th><?php echo $this->Paginator->sort('created'); ?></th>
		<th class="actions"><?php echo __d('users', 'Actions'); ?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($users as $user):
		$class = null;
		if ($i++ % 2 == 0) :
			$class = ' class="altrow"';
		endif;
		?>
		<tr<?php echo $class; ?>>
			<td><?php echo $user[$model]['username']; ?></td>
			<td><?php echo $user[$model]['created']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__d('users', 'View'), ['action' => 'view', $user[$model]['id']]); ?>
				<?php echo $this->Html->link(__d('users', 'Edit'), ['action' => 'edit', $user[$model]['id']]); ?>
				<?php echo $this->Html->link(
					__d('users', 'Delete'),
					['action' => 'delete', $user[$model]['id']],
					null,
					sprintf(__d('users', 'Are you sure you want to delete # %s?'), $user[$model]['id'])
				); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
	<?php echo $this->element('Users.paging'); ?>
</div>
<?php echo $this->element('Users.Users/sidebar'); ?>