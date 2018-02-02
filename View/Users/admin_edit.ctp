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
<div class="users form">
	<?php echo $this->Form->create($model); ?>
		<fieldset>
			<legend><?php echo __d('users', 'Edit User'); ?></legend>
			<?php
				echo $this->Form->input('id');
				echo $this->Form->input('username', [
					'label' => __d('users', 'Username')]);
				echo $this->Form->input('email', [
					'label' => __d('users', 'Email')]);
				if (!empty($roles)) {
					echo $this->Form->input('role', [
						'label' => __d('users', 'Role'), 'values' => $roles]);
				}
				echo $this->Form->input('is_admin', [
						'label' => __d('users', 'Is Admin')]);
					echo $this->Form->input('active', [
						'label' => __d('users', 'Active')]);
			?>
		</fieldset>
	<?php echo $this->Form->end('Submit'); ?>
</div>
<?php echo $this->element('Users.Users/admin_sidebar'); ?>