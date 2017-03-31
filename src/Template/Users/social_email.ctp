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
<div class="users form">
    <?= $this->Flash->render() ?>
    <?= $this->Form->create('User') ?>
    <fieldset>
        <legend><?= __d('CakeDC/Users', 'Please enter your email') ?></legend>
        <?= $this->Form->control('email') ?>
    </fieldset>
    <?= $this->Form->button(__d('CakeDC/Users', 'Submit')); ?>
    <?= $this->Form->end() ?>
</div>
