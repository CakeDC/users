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
<div class="users form">
	<fieldset>
 		<legend><?php __d('users', 'Add User');?></legend>
		<?php
		echo $this->Form->create('Openid.OpenidUser', array('url' => array('plugin' => 'openid', 'controller' => 'openid_users', 'action' => 'attach_identity')));
		$oid = isset($openIdAuthData['openid_claimed_id']) ? $openIdAuthData['openid_claimed_id'] : $openIdAuthData['openid_identity'];
		echo $this->Form->input('openid_identifier', array(
			'name' => 'data[OpenidUser][openid_url]',
			'class' => 'openid',
			'value' => $oid,
			'type' => 'hidden',
			'label' => __d('users', 'Openid Identifier', true)
			)
		);

		$username = isset($openIdAuthData['openid_sreg_nickname']) ? $openIdAuthData['openid_sreg_nickname'] : '';
		echo $this->Form->input('username', array(
			'value' => $username,
			'label' => __d('users', 'Username', true),
		));

		if (isset($this->params['named']['username_taken'])) {
			echo $this->Form->input('username', array(
				'value' => $openIdAuthData['openid_sreg_nickname'],
				'label' => __d('users', 'Username', true),
				)
			);
		}

		if (isset($openIdAuthData['openid_sreg_email'])) {
			echo $this->Form->input('email', array(
				'value' => $openIdAuthData['openid_sreg_email'],
				'label' => __d('users', 'Email', true),
				'type' => 'hidden',
				)
			);
		} elseif (isset($openIdAuthData['openid_ext1_value_email'])) {
			echo $this->Form->input('email', array(
				'value' => $openIdAuthData['openid_ext1_value_email'],
				'label' => __d('users', 'Email', true),
				'type' => 'hidden',
				)
			);
		}
		echo $this->Form->input('tos', array(
			'type' => 'checkbox',
			'label' => __d('users', 'I have read and agreed to ', true) . $this->Html->link(__d('users', 'Terms of Service', true), array('controller' => 'pages', 'action' => 'tos')), 
			'error' => __d('users', 'You must verify you have read the Terms of Service', true)
			)
		);
		echo $this->Form->end(__d('users', 'Submit',true));
		?>
	</fieldset>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__d('users', 'List Users', true), array('action'=>'index'));?></li>
	</ul>
</div>
