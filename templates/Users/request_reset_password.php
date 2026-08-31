<?php
/**
 * Copyright 2010 - 2020, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2020, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * @var \CakeDC\Users\Model\Entity\User $user
 */
?>
<?php // Full page wraps the form in the #ajax-login-container swap target; the HTMX
      // fragment re-render (swapped *into* that container) omits the wrapper. ?>
<?php if (($ajaxEnabled ?? false) && !($ajaxFragment ?? false)) : ?>
<div id="ajax-login-container">
<?php endif; ?>
<div class="users form">
    <?= $this->Flash->render('auth') ?>
    <?= $this->Form->create($user, ($ajaxEnabled ?? false) ? [
        'hx-post' => $this->Url->build(['action' => 'requestResetPassword']),
        'hx-target' => '#ajax-login-container',
        'hx-swap' => 'innerHTML',
    ] : []) ?>
    <fieldset>
        <legend><?= __d('cake_d_c/users', 'Please enter your email or username to reset your password') ?></legend>
        <?= $this->Form->control('reference') ?>
    </fieldset>
    <?= $this->Form->button(__d('cake_d_c/users', 'Submit')); ?>
    <?= $this->Form->end() ?>
</div>
<?php if (($ajaxEnabled ?? false) && !($ajaxFragment ?? false)) : ?>
</div>
<?php endif; ?>
