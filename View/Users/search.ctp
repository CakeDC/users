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
<h2><?php echo __d('users', 'Search for users'); ?></h2>
<?php 
echo $this->Form->create($model, array('action' => 'search'));
	echo $this->Form->input('username', array('label' => __d('users', 'Username')));
	echo $this->Form->input('email', array('label' => __d('users', 'Email')));
	echo $this->Form->input('Profile.name', array('label' => __d('users', 'Name')));
echo $this->Form->end(__d('users', 'Search'));
