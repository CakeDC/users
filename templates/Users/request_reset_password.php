<div class="users form">
    <?= $this->Flash->render('auth') ?>
    <?= $this->Form->create('User') ?>
    <fieldset>
        <legend><?= __d('cake_d_c/users', 'Please enter your email to reset your password') ?></legend>
        <?= $this->Form->control('reference') ?>
    </fieldset>
    <?= $this->Form->button(__d('cake_d_c/users', 'Submit')); ?>
    <?= $this->Form->end() ?>
</div>
