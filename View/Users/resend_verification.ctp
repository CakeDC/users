<div class="users resend-verification">
	<h2><?php echo __d('users', 'Resend the Email Verification'); ?></h2>
	<fieldset>
		<?php
			echo $this->Form->create($model, array(
				'action' => 'login',
				'id' => 'ResendVerficationForm'));
			echo $this->Form->input('email', array(
				'label' => __d('users', 'Email')));
			echo $this->Form->end(__d('users', 'Submit'));
		?>
	</fieldset>
</div>
<?php echo $this->element('Users/sidebar'); ?>