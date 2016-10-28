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
    <h3><?= __d('CakeDC/Users', 'Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__d('CakeDC/Users', 'List Users'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__d('CakeDC/Users', 'List Accounts'), ['controller' => 'Accounts', 'action' => 'index']) ?> </li>
    </ul>
</div>
<div class="users form large-10 medium-9 columns">
    <?= $this->Form->create(${$tableAlias}); ?>
    <fieldset>
        <legend><?= __d('CakeDC/Users', 'Add User') ?></legend>
        <?php
            echo $this->Form->input('username', ['label' => __d('CakeDC/Users', 'Username')]);
            echo $this->Form->input('email', ['label' => __d('CakeDC/Users', 'Email')]);
            echo $this->Form->input('first_name', ['label' => __d('CakeDC/Users', 'First name')]);
            echo $this->Form->input('last_name', ['label' => __d('CakeDC/Users', 'Last name')]);
            echo $this->Form->input('active', [
                'type' => 'checkbox',
                'label' => __d('CakeDC/Users', 'Active')
            ]);
        ?>
    </fieldset>
    <?= $this->Form->button(__d('CakeDC/Users', 'Submit')) ?>
    <?= $this->Form->end() ?>
</div>
