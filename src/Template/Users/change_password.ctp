<div class="users form">
    <?= $this->Flash->render('auth') ?>
    <?= $this->Form->create($user) ?>
    <fieldset>
        <legend><?= __d('CakeDC/Users', 'Please enter the new password') ?></legend>
        <?php if ($validatePassword) : ?>
            <?= $this->Form->input('current_password', [
                'type' => 'password',
                'required' => true,
                'label' => __d('CakeDC/Users', 'Current password')]);
            ?>
        <?php endif; ?>
        <?= $this->Form->input('password', [
            'type' => 'password',
            'required' => true,
            'label' => __d('CakeDC/Users', 'New password')]);
        ?>
        <?= $this->Form->input('password_confirm', [
            'type' => 'password',
            'required' => true,
            'label' => __d('CakeDC/Users', 'Confirm password')]);
        ?>

    </fieldset>
    <?= $this->Form->button(__d('CakeDC/Users', 'Submit')); ?>
    <?= $this->Form->end() ?>
</div>