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
<?php $openIdAuthData = $this->Session->read('openIdAuthData'); ?>
<h2><?php echo __d('users', 'Account registration'); ?></h2>
<fieldset>
	<legend><?php echo __d('users', 'Details'); ?></legend>
	<?php
	if (!isset($openIdAuthData)) {
		echo $this->Form->create($model, array('url' => array('action'=>'register')));
		echo $this->Form->input('username', array(
			'error' => 	array(
				'unique_username' => __d('users', 'Please select a username that is not already in use'),
				'username_min' => __d('users', 'Must be at least 3 characters'),
				'alpha' => __d('users', 'Username must contain numbers and letters only'),
				'required' => __d('users', 'Please choose username'))
		));
		echo $this->Form->input('email', array(
			'label' => __d('users', 'E-mail (used as login)'),
			'error' => array('isValid' => __d('users', 'Must be a valid email address'),
				'isUnique' => __d('users', 'An account with that email already exists'))
		));
		echo $this->Form->input('password', array(
			'label' => __d('users', 'Password'),
			'error' => __d('users', 'Must be at least 5 characters long')
		));
		echo $this->Form->input('temppassword', array(
			'label' => __d('users', 'Password (confirm)'),
			'type' => 'password',
			'error' => __d('users', 'Passwords must match')
		));
		echo $this->Form->input('tos', array(
			'label' => __d('users', 'I have read and agreed to ') . $this->Html->link(__d('users', 'Terms of Service'), array('controller' => 'pages', 'action' => 'tos')), 
			'error' => __d('users', 'You must verify you have read the Terms of Service')
		));
		echo $this->Form->end(__d('users', 'Submit'));
	} else {
		if (isset($openIdAuthData['openid_claimed_id'])) {
			$oid = $openIdAuthData['openid_claimed_id'];
		} else {
			$oid = $openIdAuthData['openid_identity'];
		}
			echo $this->Form->create('Openid.OpenidUser', array(
			'url' => array('plugin' => 'openid', 'controller' => 'openid_users', 'action' => 'attach_identity')
		));
		echo $this->Form->input('openid_identifier', array(
			'name' => 'data[OpenidUser][openid_url]',
			'class' => 'openid',
			'value' => $oid,
			'type' => 'hidden',
			'label' => __d('users', 'Openid Identifier')
		));

		if (isset($openIdAuthData['openid_sreg_nickname'])) {
			$username = $openIdAuthData['openid_sreg_nickname'];
		} else {
			$username = '';
		}
		echo $this->Form->input('username', array(
			'value' => $username,
			'label' => __d('users', 'Username'),
		));
		
		if (isset($this->params['named']['username_taken'])) {
			echo $this->Form->input('username', array(
				'value' => $openIdAuthData['openid_sreg_nickname'],
				'label' => __d('users', 'Username'),
			));
		}

		if (isset($openIdAuthData['openid_sreg_email'])) {
			echo $this->Form->input('email', array(
				'value' => $openIdAuthData['openid_sreg_email'],
				'label' => __d('users', 'Email'),
				'type' => 'hidden',
			));
		} elseif (isset($openIdAuthData['openid_ext1_value_email'])) {
			echo $this->Form->input('email', array(
				'value' => $openIdAuthData['openid_ext1_value_email'],
				'label' => __d('users', 'Email'),
				'type' => 'hidden',
			));
		}
		echo $this->Form->input('tos', array(
			'type' => 'checkbox',
			'label' => __d('users', 'I have read and agreed to ') . $this->Html->link(__d('users', 'Terms of Service'), array('controller' => 'pages', 'action' => 'tos')), 
			'error' => __d('users', 'You must verify you have read the Terms of Service')
		));
		echo $this->Form->end(__d('users', 'Submit'));
	}
	?>
</fieldset>