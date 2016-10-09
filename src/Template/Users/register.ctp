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
use Cake\Core\Configure;
?>
<div class="users form large-10 medium-9 columns">
    <?= $this->Form->create($user); ?>
    <fieldset>
        <legend><?= __d('CakeDC/Users', 'Add User') ?></legend>
        <?php
        echo $this->Form->input('username');
        echo $this->Form->input('email');
        echo $this->Form->input('password');
        echo $this->Form->input('password_confirm', ['type' => 'password']);
        echo $this->Form->input('first_name');
        echo $this->Form->input('last_name');
        if (Configure::read('Users.Tos.required')) {
            echo $this->Form->input('tos', ['type' => 'checkbox', 'label' => __d('CakeDC/Users', 'Accept TOS conditions?'), 'required' => true]);
        }
        if (Configure::read('Users.reCaptcha.registration')) {
            echo $this->User->addReCaptcha();
        }
        ?>
    </fieldset>
    <?= $this->Form->button(__d('CakeDC/Users', 'Submit')) ?>
    <?= $this->Form->end() ?>
</div>
