<?php
/**
 * Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

use Cake\Core\Configure;

?>
<div class="users form">
    <?= $this->Flash->render('auth') ?>
    <?= $this->Form->create(null, ($ajaxEnabled ?? false) ? [
        'hx-post' => $this->Url->build(['action' => 'login']),
        'hx-target' => '#ajax-login-container',
        'hx-swap' => 'innerHTML',
    ] : []) ?>
    <fieldset>
        <legend><?= __d('cake_d_c/users', 'Please enter your username and password') ?></legend>
        <?= $this->Form->control('username', ['label' => __d('cake_d_c/users', 'Username'), 'required' => true, 'autofocus' => 'autofocus']) ?>
        <?= $this->Form->control('password', ['label' => __d('cake_d_c/users', 'Password'), 'required' => true]) ?>
        <?php
        if (Configure::read('Users.reCaptcha.login')) {
            echo $this->User->addReCaptcha();
        }
        if (Configure::read('Users.RememberMe.active')) {
            echo $this->Form->control(Configure::read('Users.Key.Data.rememberMe'), [
                'type' => 'checkbox',
                'label' => __d('cake_d_c/users', 'Remember me'),
                'checked' => Configure::read('Users.RememberMe.checked')
            ]);
        }
        ?>
        <?php
        $registrationActive = Configure::read('Users.Registration.active');
        if ($registrationActive) {
            echo $this->Html->link(__d('cake_d_c/users', 'Register'), ['action' => 'register']);
        }
        if (Configure::read('Users.Email.required')) {
            if ($registrationActive) {
                echo ' | ';
            }
            echo $this->Html->link(__d('cake_d_c/users', 'Reset Password'), ['action' => 'requestResetPassword']);
            if (Configure::read('OneTimeLogin.enabled')) {
                echo ' | ';
                echo $this->Html->link(__d('cake_d_c/users', 'Send me a login link'), ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'requestLoginLink'], ['allowed' => true, 'escape' => false]);
            }
        }
        ?>
    </fieldset>
    <?= implode(' ', $this->User->socialLoginList()); ?>
    <?= $this->User->button(__d('cake_d_c/users', 'Login')); ?>
    <?= $this->Form->end() ?>
</div>
