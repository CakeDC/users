<div class="container">
    <div class="row">
        <div class="col-xs-offset-1 col-xs-10 col-sm-offset-2 col-sm-8 col-md-6 col-md-offset-3">
            <div class="users form well well-lg">
                <?= $this->Form->create() ?>

                <?= $this->Flash->render('auth') ?>
                <?= $this->Flash->render() ?>
                <fieldset>
                    <?= $this->Form->control($field, ['required' => true, 'label' => false, 'placeholder' => __d('cake_d_c/users', 'Enter {0}', $field)]) ?>
                </fieldset>
                <?= $this->Form->button(__d('cake_d_c/users', '<span class="glyphicon glyphicon-log-in" aria-hidden="true"></span> Register {0}', $field), ['class' => 'btn btn-primary', 'escapeTitle' => false]); ?>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
