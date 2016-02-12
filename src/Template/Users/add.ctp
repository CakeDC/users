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
<div class="actions columns large-2 medium-3">
    <h3><?= __d('Users', 'Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__d('Users', 'List Users'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__d('Users', 'List Accounts'), ['controller' => 'Accounts', 'action' => 'index']) ?> </li>
    </ul>
</div>
<div class="users form large-10 medium-9 columns">
    <?= $this->Form->create($Users); ?>
    <fieldset>
        <legend><?= __d('Users', 'Add User') ?></legend>
        <?php
            echo $this->Form->input('username');
            echo $this->Form->input('email');
            echo $this->Form->input('password');
            echo $this->Form->input('first_name');
            echo $this->Form->input('last_name');
            echo $this->Form->input('active', ['type' => 'checkbox']);
        ?>
    </fieldset>
    <?= $this->Form->button(__d('Users', 'Submit')) ?>
    <?= $this->Form->end() ?>
</div>
