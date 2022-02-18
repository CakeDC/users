<div class="container">
    <div class="row">
        <div class="col-xs-offset-1 col-xs-10 col-sm-offset-2 col-sm-8 col-md-6 col-md-offset-3">
            <div class="users form well well-lg">
                <?= $this->Form->create() ?>

                <?= $this->Flash->render('auth') ?>
                <?= $this->Flash->render() ?>
                <fieldset>
                    <?php if (!$verified) : ?>
                        <p><?= __d('cake_d_c/users', 'You need to verify the {0} entered.', $field) ?></p>
                    <?php endif; ?>
                    <p><?= __d('cake_d_c/users', 'We have sent a verification code to: {0}', $this->Html->tag('i', $masked)) ?></p>
                    <?= $this->Form->control('code', ['required' => true,'label' => false, 'placeholder' => __d('cake_d_c/users', 'Verification Code')]) ?>
                </fieldset>
                <div class="row">
                    <div class="col-12">

                        <?= $this->Form->postLink(__d('cake_d_c/users', 'Resend Code'), ['?' => ['resend' => true]], ['block' => 'resendCode']); ?>
                    </div>

                </div>
                <?= $this->Form->button(__d('cake_d_c/users', '<span class="glyphicon glyphicon-log-in" aria-hidden="true"></span> Verify'), ['class' => 'btn btn-primary', 'escapeTitle' => false]); ?>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
<?= $this->fetch('resendCode') ?>
