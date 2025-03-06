<?php
/**
 * Copyright 2010 - 2025, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2025, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

?>
<div class="users form">
    <?= $this->Flash->render('auth') ?>
    <?= $this->Form->create(null) ?>
    <fieldset>
        <legend><?= __d('cake_d_c/users', 'Please enter your email to receive a link to login.') ?></legend>
        <?= $this->Form->control('email') ?>
        <?php if (\Cake\Core\Configure::read('Users.reCaptcha.login')) {
            echo $this->User->addReCaptcha();
        } ?>
    </fieldset>
    <?= $this->User->button(__d('cake_d_c/users', 'Submit')); ?>
    <?= $this->Form->end() ?>
</div>
