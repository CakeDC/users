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
<h2><?php echo __d('users', 'Detail'); ?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"'; ?>
		<dt<?php if ($i % 2 == 0) echo $class; ?>><?php echo __d('users', 'Id'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class; ?>>
			<?php echo $detail['Detail']['id']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class; ?>><?php echo __d('users', 'User'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class; ?>>
			<?php echo $this->Html->link($detail['User']['id'], array('controller' => 'users', 'action' => 'view', $detail['User']['id'])); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class; ?>><?php echo __d('users', 'Position'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class; ?>>
			<?php echo $detail['Detail']['position']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class; ?>><?php echo __d('users', 'Field'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class; ?>>
			<?php echo $detail['Detail']['field']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class; ?>><?php echo __d('users', 'Value'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class; ?>>
			<?php echo $detail['Detail']['value']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class; ?>><?php echo __d('users', 'Created'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class; ?>>
			<?php echo $detail['Detail']['created']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class; ?>><?php echo __d('users', 'Modified'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class; ?>>
			<?php echo $detail['Detail']['modified']; ?>
			&nbsp;
		</dd>
	</dl>
</div>

<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__d('users', 'Edit Detail'), array('action' => 'edit', $detail['Detail']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__d('users', 'Delete Detail'), array('action' => 'delete', $detail['Detail']['id']), null, sprintf(__d('users', 'Are you sure you want to delete # %s?'), $detail['Detail']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__d('users', 'List Details'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__d('users', 'New Detail'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__d('users', 'List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__d('users', 'New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
