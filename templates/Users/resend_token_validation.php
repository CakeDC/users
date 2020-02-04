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
<div class="users form large-10 medium-9 columns">
    <?= $this->Form->create($user); ?>
    <fieldset>
        <legend><?= __d('cake_d_c/users', 'Resend Validation email') ?></legend>
        <?php
        echo $this->Form->control('reference', ['label' => __d('cake_d_c/users', 'Email or username')]);
        ?>
    </fieldset>
    <?= $this->Form->button(__d('cake_d_c/users', 'Submit')) ?>
    <?= $this->Form->end() ?>
</div>
