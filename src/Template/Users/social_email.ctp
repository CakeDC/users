<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<div class="users form">
    <?= $this->Flash->render('auth') ?>
    <?= $this->Form->create('User', ['action' => 'login']) ?>
    <fieldset>
        <legend><?= __d('Users', 'Please enter your email') ?></legend>
        <?= $this->Form->input('email') ?>
    </fieldset>
    <?= $this->Form->button(__d('Users', 'Submit')); ?>
    <?= $this->Form->end() ?>
</div>
