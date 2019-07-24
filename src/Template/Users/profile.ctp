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
<div class="users">
    <h3><?= $this->Html->image(
            empty($user->avatar) ? $avatarPlaceholder : $user->avatar,
            ['width' => '180', 'height' => '180']
        ); ?></h3>
    <h3>
        <?=
        $this->Html->tag(
            'span',
            __d('cake_d_c/users', '{0} {1}', $user->first_name, $user->last_name),
            ['class' => 'full_name']
        )
        ?>
    </h3>
    <?php //@todo add to config ?>
    <?= $this->Html->link(__d('cake_d_c/users', 'Change Password'), ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'changePassword']); ?>
    <div class="row">
        <div class="large-6 columns strings">
            <h6 class="subheader"><?= __d('cake_d_c/users', 'Username') ?></h6>
            <p><?= h($user->username) ?></p>
            <h6 class="subheader"><?= __d('cake_d_c/users', 'Email') ?></h6>
            <p><?= h($user->email) ?></p>
            <?= $this->User->socialConnectLinkList($user->social_accounts) ?>
            <?php
            if (!empty($user->social_accounts)):
                ?>
                <h6 class="subheader"><?= __d('cake_d_c/users', 'Social Accounts') ?></h6>
                <table cellpadding="0" cellspacing="0">
                    <thead>
                    <tr>
                        <th><?= __d('cake_d_c/users', 'Avatar'); ?></th>
                        <th><?= __d('cake_d_c/users', 'Provider'); ?></th>
                        <th><?= __d('cake_d_c/users', 'Link'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($user->social_accounts as $socialAccount):
                        $escapedUsername = h($socialAccount->username);
                        $linkText = empty($escapedUsername) ? __d('cake_d_c/users', 'Link to {0}', h($socialAccount->provider)) : h($socialAccount->username)
                        ?>
                        <tr>
                            <td><?=
                                $this->Html->image(
                                    $socialAccount->avatar,
                                    ['width' => '90', 'height' => '90']
                                ) ?>
                            </td>
                            <td><?= h($socialAccount->provider) ?></td>
                            <td><?=
                                $socialAccount->link && $socialAccount->link != '#' ? $this->Html->link(
                                    $linkText,
                                    $socialAccount->link,
                                    ['target' => '_blank']
                                ) : '-' ?></td>
                        </tr>
                        <?php
                    endforeach;
                    ?>
                    </tbody>
                </table>
                <?php
            endif;
            ?>
        </div>
    </div>
</div>
