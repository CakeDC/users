<?php
/**
 * Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<div class="actions columns large-2 medium-3">
    <h3><?= __d('CakeDC/Users', 'Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__d('CakeDC/Users', 'List Users'), ['action' => 'index']) ?></li>
    </ul>
</div>
<div class="users form large-10 medium-9 columns">
    <?= $this->Form->create(${$tableAlias}); ?>
    <fieldset>
        <legend><?= __d('CakeDC/Users', 'Add User') ?></legend>
        <?php
            echo $this->Form->control('username', ['label' => __d('CakeDC/Users', 'Username')]);
            echo $this->Form->control('email', ['label' => __d('CakeDC/Users', 'Email')]);
            echo $this->Form->control('password', ['label' => __d('CakeDC/Users', 'Password')]);
            echo $this->Form->control('first_name', ['label' => __d('CakeDC/Users', 'First name')]);
            echo $this->Form->control('last_name', ['label' => __d('CakeDC/Users', 'Last name')]);
            echo $this->Form->control('active', [
                'type' => 'checkbox',
                'label' => __d('CakeDC/Users', 'Active')
            ]);
        ?>
    </fieldset>
    <?= $this->Form->button(__d('CakeDC/Users', 'Submit')) ?>
    <?= $this->Form->end() ?>
</div>
