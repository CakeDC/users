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
    <h3><?= __d('Users', 'Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__d('Users', 'Edit User'), ['action' => 'edit', $Users->id]) ?> </li>
        <li><?= $this->Form->postLink(__d('Users', 'Delete User'), ['action' => 'delete', $Users->id], ['confirm' => __d('Users', 'Are you sure you want to delete # {0}?', $Users->id)]) ?> </li>
        <li><?= $this->Html->link(__d('Users', 'List Users'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__d('Users', 'New User'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__d('Users', 'List Accounts'), ['controller' => 'Accounts', 'action' => 'index']) ?> </li>
    </ul>
</div>
<div class="users view large-10 medium-9 columns">
    <h2><?= h($Users->id) ?></h2>
    <div class="row">
        <div class="large-5 columns strings">
            <h6 class="subheader"><?= __d('Users', 'Id') ?></h6>
            <p><?= h($Users->id) ?></p>
            <h6 class="subheader"><?= __d('Users', 'Username') ?></h6>
            <p><?= h($Users->username) ?></p>
            <h6 class="subheader"><?= __d('Users', 'Email') ?></h6>
            <p><?= h($Users->email) ?></p>
            <h6 class="subheader"><?= __d('Users', 'First Name') ?></h6>
            <p><?= h($Users->first_name) ?></p>
            <h6 class="subheader"><?= __d('Users', 'Last Name') ?></h6>
            <p><?= h($Users->last_name) ?></p>
            <h6 class="subheader"><?= __d('Users', 'Token') ?></h6>
            <p><?= h($Users->token) ?></p>
            <h6 class="subheader"><?= __d('Users', 'Api Token') ?></h6>
            <p><?= h($Users->api_token) ?></p>
        </div>
        <div class="large-2 columns numbers end">
            <h6 class="subheader"><?= __d('Users', 'Active') ?></h6>
            <p><?= $this->Number->format($Users->active) ?></p>
        </div>
        <div class="large-2 columns dates end">
            <h6 class="subheader"><?= __d('Users', 'Token Expires') ?></h6>
            <p><?= h($Users->token_expires) ?></p>
            <h6 class="subheader"><?= __d('Users', 'Activation Date') ?></h6>
            <p><?= h($Users->activation_date) ?></p>
            <h6 class="subheader"><?= __d('Users', 'Tos Date') ?></h6>
            <p><?= h($Users->tos_date) ?></p>
            <h6 class="subheader"><?= __d('Users', 'Created') ?></h6>
            <p><?= h($Users->created) ?></p>
            <h6 class="subheader"><?= __d('Users', 'Modified') ?></h6>
            <p><?= h($Users->modified) ?></p>
        </div>
    </div>
</div>
<div class="related row">
    <div class="column large-12">
        <h4 class="subheader"><?= __d('Users', 'Related Accounts') ?></h4>
        <?php if (!empty($Users->social_accounts)): ?>
            <table cellpadding="0" cellspacing="0">
                <tr>
                    <th><?= __d('Users', 'Id') ?></th>
                    <th><?= __d('Users', 'User Id') ?></th>
                    <th><?= __d('Users', 'Provider') ?></th>
                    <th><?= __d('Users', 'Username') ?></th>
                    <th><?= __d('Users', 'Reference') ?></th>
                    <th><?= __d('Users', 'Avatar') ?></th>
                    <th><?= __d('Users', 'Token') ?></th>
                    <th><?= __d('Users', 'Token Expires') ?></th>
                    <th><?= __d('Users', 'Active') ?></th>
                    <th><?= __d('Users', 'Data') ?></th>
                    <th><?= __d('Users', 'Created') ?></th>
                    <th><?= __d('Users', 'Modified') ?></th>
                    <th class="actions"><?= __d('Users', 'Actions') ?></th>
                </tr>
                <?php foreach ($Users->social_accounts as $socialAccount): ?>
                    <tr>
                        <td><?= h($socialAccount->id) ?></td>
                        <td><?= h($socialAccount->user_id) ?></td>
                        <td><?= h($socialAccount->provider) ?></td>
                        <td><?= h($socialAccount->username) ?></td>
                        <td><?= h($socialAccount->reference) ?></td>
                        <td><?= h($socialAccount->avatar) ?></td>
                        <td><?= h($socialAccount->token) ?></td>
                        <td><?= h($socialAccount->token_expires) ?></td>
                        <td><?= h($socialAccount->active) ?></td>
                        <td><?= h($socialAccount->data) ?></td>
                        <td><?= h($socialAccount->created) ?></td>
                        <td><?= h($socialAccount->modified) ?></td>

                        <td class="actions">
                            <?= $this->Html->link(__d('Users', 'View'), ['controller' => 'Accounts', 'action' => 'view', $socialAccount->id]) ?>

                            <?= $this->Html->link(__d('Users', 'Edit'), ['controller' => 'Accounts', 'action' => 'edit', $socialAccount->id]) ?>

                            <?= $this->Form->postLink(__d('Users', 'Delete'), ['controller' => 'Accounts', 'action' => 'delete', $socialAccount->id], ['confirm' => __d('Users', 'Are you sure you want to delete # {0}?', $accounts->id)]) ?>

                        </td>
                    </tr>

        <?php endforeach; ?>
            </table>
            <?php endif; ?>
    </div>
</div>
