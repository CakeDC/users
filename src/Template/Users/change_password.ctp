<div class="users form">
    <?= $this->Flash->render('auth') ?>
    <?= $this->Form->create($user) ?>
    <fieldset>
        <legend><?= __d('Users', 'Please enter the new password') ?></legend>
        <?php if ($validatePassword) : ?>
            <?= $this->Form->input('current_password', ['type' => 'password', 'required' => true, 'label' => __d('Users', 'Current password')]); ?>
        <?php endif; ?>
        <?= $this->Form->input('password'); ?>
        <?= $this->Form->input('password_confirm', ['type' => 'password', 'required' => true]); ?>

    </fieldset>
    <?= $this->Form->button(__d('Users', 'Submit')); ?>
    <?= $this->Form->end() ?>
</div>