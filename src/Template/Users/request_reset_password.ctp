<div class="users form">
    <?= $this->Flash->render('auth') ?>
    <?= $this->Form->create('User') ?>
    <fieldset>
        <legend><?= __d('Users', 'Please enter your email to reset your password') ?></legend>
        <?= $this->Form->input('reference') ?>
    </fieldset>
    <?= $this->Form->button(__d('Users', 'Submit')); ?>
    <?= $this->Form->end() ?>
</div>
