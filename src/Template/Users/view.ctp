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

$Users = ${$tableAlias};
?>
<div class="actions columns large-2 medium-3">
    <h3><?= __d('CakeDC/Users', 'Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__d('CakeDC/Users', 'Edit User'), ['action' => 'edit', $Users->id]) ?> </li>
        <li><?= $this->Form->postLink(
                __d('CakeDC/Users', 'Delete User'),
                ['action' => 'delete', $Users->id],
                ['confirm' => __d('CakeDC/Users', 'Are you sure you want to delete # {0}?', $Users->id)]
            ) ?> </li>
        <li><?= $this->Html->link(__d('CakeDC/Users', 'List Users'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__d('CakeDC/Users', 'New User'), ['action' => 'add']) ?> </li>
    </ul>
</div>
<div class="users view large-10 medium-9 columns">
    <h2><?= h($Users->id) ?></h2>
    <div class="row">
        <div class="large-5 columns strings">
            <h6 class="subheader"><?= __d('CakeDC/Users', 'Id') ?></h6>
            <p><?= h($Users->id) ?></p>
            <h6 class="subheader"><?= __d('CakeDC/Users', 'Username') ?></h6>
            <p><?= h($Users->username) ?></p>
            <h6 class="subheader"><?= __d('CakeDC/Users', 'Email') ?></h6>
            <p><?= h($Users->email) ?></p>
            <h6 class="subheader"><?= __d('CakeDC/Users', 'First Name') ?></h6>
            <p><?= h($Users->first_name) ?></p>
            <h6 class="subheader"><?= __d('CakeDC/Users', 'Last Name') ?></h6>
            <p><?= h($Users->last_name) ?></p>
            <h6 class="subheader"><?= __d('CakeDC/Users', 'Role') ?></h6>
            <p><?= h($Users->role) ?></p>
            <h6 class="subheader"><?= __d('CakeDC/Users', 'Token') ?></h6>
            <p><?= h($Users->token) ?></p>
            <h6 class="subheader"><?= __d('CakeDC/Users', 'Api Token') ?></h6>
            <p><?= h($Users->api_token) ?></p>
        </div>
        <div class="large-2 columns numbers end">
            <h6 class="subheader"><?= __d('CakeDC/Users', 'Active') ?></h6>
            <p><?= $this->Number->format($Users->active) ?></p>
        </div>
        <div class="large-2 columns dates end">
            <h6 class="subheader"><?= __d('CakeDC/Users', 'Token Expires') ?></h6>
            <p><?= h($Users->token_expires) ?></p>
            <h6 class="subheader"><?= __d('CakeDC/Users', 'Activation Date') ?></h6>
            <p><?= h($Users->activation_date) ?></p>
            <h6 class="subheader"><?= __d('CakeDC/Users', 'Tos Date') ?></h6>
            <p><?= h($Users->tos_date) ?></p>
            <h6 class="subheader"><?= __d('CakeDC/Users', 'Created') ?></h6>
            <p><?= h($Users->created) ?></p>
            <h6 class="subheader"><?= __d('CakeDC/Users', 'Modified') ?></h6>
            <p><?= h($Users->modified) ?></p>
        </div>
    </div>
</div>
<div class="related row">
    <div class="column large-12">
        <h4 class="subheader"><?= __d('CakeDC/Users', 'Social Accounts') ?></h4>
        <?php if (!empty($Users->social_accounts)) : ?>
            <table cellpadding="0" cellspacing="0">
                <tr>
                    <th><?= __d('CakeDC/Users', 'Provider') ?></th>
                    <th><?= __d('CakeDC/Users', 'Avatar') ?></th>
                    <th><?= __d('CakeDC/Users', 'Active') ?></th>
                    <th><?= __d('CakeDC/Users', 'Created') ?></th>
                    <th><?= __d('CakeDC/Users', 'Modified') ?></th>
                </tr>
                <?php foreach ($Users->social_accounts as $socialAccount) : ?>
                    <tr>
                        <td><?= h($socialAccount->provider) ?></td>
                        <td><?= h($socialAccount->avatar) ?></td>
                        <td><?= h($socialAccount->active) ?></td>
                        <td><?= h($socialAccount->created) ?></td>
                        <td><?= h($socialAccount->modified) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</div>
