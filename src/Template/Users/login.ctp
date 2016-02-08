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
<div class="users form">
    <?= $this->Flash->render('auth') ?>
    <?= $this->Form->create() ?>
    <fieldset>
        <legend><?= __d('Users', 'Please enter your username and password') ?></legend>
        <?= $this->Form->input('username', ['required' => true]) ?>
        <?= $this->Form->input('password', ['required' => true]) ?>
        <?php
        if (Configure::check('Users.RememberMe.active')) {
            echo $this->Form->input(Configure::read('Users.Key.Data.rememberMe'), [
                'type' => 'checkbox',
                'label' => __d('Users', 'Remember me'),
                'checked' => 'checked'
            ]);
        }
        ?>
        <p>
            <?php
            $registrationActive = Configure::read('Users.Registration.active');
            if ($registrationActive) {
                echo $this->Html->link(__d('users', 'Register'), ['action' => 'register']);
            }
            if (Configure::read('Users.Email.required')) {
                if ($registrationActive) {
                    echo ' | ';
                }
                echo $this->Html->link(__d('users', 'Reset Password'), ['action' => 'requestResetPassword']);
            }
            ?>
        </p>
    </fieldset>
    <?php if (Configure::read('Users.Social.login')) : ?>
        <?php $providers = Configure::read('OAuth.providers'); ?>
        <?php foreach ($providers as $provider => $options) : ?>
            <?php if (!empty($options['options']['redirectUri'])) : ?>
                <?= $this->User->socialLogin($provider); ?>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php
    endif; ?>
    <?= $this->Form->button(__d('Users', 'Login')); ?>
    <?= $this->Form->end() ?>
</div>
