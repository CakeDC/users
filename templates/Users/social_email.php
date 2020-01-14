<?php
/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<div class="users form">
    <?= $this->Flash->render() ?>
    <?= $this->Form->create() ?>
    <fieldset>
        <legend><?= __d('cake_d_c/users', 'Please enter your email') ?></legend>
        <?= $this->Form->control('email', ['type' => 'email', 'required' => true]) ?>
    </fieldset>
    <?= $this->Form->button(__d('cake_d_c/users', 'Submit')); ?>
    <?= $this->Form->end() ?>
</div>
