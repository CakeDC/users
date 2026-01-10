<?php
/**
 * Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<style type="text/css">
    .token-inputs input {
        width: 50px;
        height: 50px;
    }
</style>
<div class="text-center">
    <h4><?= __('Enter Your One-Time Login Token') ?></h4>
</div>

<?php if (isset($remainingTime) && $remainingTime > 0): ?>
    <div id="timer" class="alert alert-warning mt-5 mb-5">
        <?= __d('cake_d_c/users', 'Please wait <span id="remaining-time">{0}}</span>. We will send you a new token soon.', h($remainingTime)) ?>
    </div>
<?php endif; ?>

<div class="token-input-container">
    <?= $this->Form->create(null, ['url' => ['action' => 'singleTokenLogin']]) ?>
    <div class="token-inputs">
        <?php for ($i = 0; $i < 6; $i++): ?>
            <?= $this->Form->text("token[$i]", [
                'label' => false,
                'maxlength' => 1,
                'class' => 'token-input',
                'autocomplete' => 'off',
                'required' => true,
                'pattern' => '[0-9]*',
                'inputmode' => 'numeric',
                'onkeyup' => "moveToNext(this, event)",
                'oninput' => "this.value = this.value.replace(/[^0-9]/g, '')"
            ]) ?>
        <?php endfor; ?>
    </div>
    <div class="row">
        <div class="col-xs-6 col-xs-offset-3">
            <?=  $this->Html->link(__('Request new token'), ['action' => 'requestLoginLink'], ['class' => 'btn btn-primary btn-block btn-flat', 'style' => 'margin: auto;']) ?>
        </div>
    </div>

    <?= $this->Form->end() ?>
</div>

<?php if (isset($remainingTime) && $remainingTime > 0): ?>
    <script>
        let remainingTime = <?= h($remainingTime) ?>;
        const countdownElement = document.getElementById('remaining-time');
        const inputFields = document.querySelectorAll('input, button');

        inputFields.forEach(input => {
            input.disabled = true;
        });

        const countdown = setInterval(() => {
            if (remainingTime > 0) {
                countdownElement.textContent = remainingTime;
                remainingTime--;
            } else {
                clearInterval(countdown);
                countdownElement.textContent = '0';
                inputFields.forEach(input => {
                    input.disabled = false;
                });
                document.getElementById('timer').remove();
                const firstInput = document.querySelector('#token-0');
                if (firstInput) {
                    firstInput.focus();
                }
            }
        }, 1000);
    </script>
<?php endif; ?>
<script>
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(alert => {
            alert.remove();
        });
    }, 10000);
</script>
<?= $this->Html->script('CakeDC/Users.singleTokenLogin', ['block' => 'script']); ?>
