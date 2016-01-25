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
<div class="users form large-10 medium-9 columns">
    <?= $this->Form->create($user); ?>
    <fieldset>
        <legend><?= __d('Users', 'Resend Validation email') ?></legend>
        <?php
        echo $this->Form->input('reference', ['label' => __d('Users', 'Email or username')]);
        ?>
    </fieldset>
    <?= $this->Form->button(__d('Users', 'Submit')) ?>
    <?= $this->Form->end() ?>
</div>
