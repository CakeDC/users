<?php
/**
 * @var \App\View\AppView $this
 */
    $this->Html->script('CakeDC/Users.u2f-api.js', ['block' => true]);
?>
<div class="container">
    <div class="row">
        <div class="col-xs-offset-1 col-xs-10 col-sm-offset-2 col-sm-8 col-md-6 col-md-offset-3">
            <div class="users form well well-lg">
                <?= $this->Form->create(null, [
                    'url' => [
                        'action' => 'u2fAuthenticateFinish',
                        '?' => $this->request->getQueryParams()
                    ],
                    'id' => 'u2fAuthenticateFrm'
                ]) ?>

                <?= $this->Flash->render('auth') ?>
                <?= $this->Flash->render() ?>
                <fieldset>
                    <h2 class='text-center'><?= __d('cake_d_c/users', 'Verify your registered yubico key')?> </h2>
                    <h3 class='text-center'><?= __d('cake_d_c/users', 'Please insert and tap your yubico key')?></h3>
                    <p><?__( 'You can now finish the authentication process using the registered device.')?></p>
                    <p><?= __d('cake_d_c/users', 'When the YubiKey starts blinking, press the golden disc to activate it. Depending on the web browser you might need to confirm the use of extended information from the YubiKey.')?></p>
                    <p class="text-center"><?= $this->Html->link(
                        __d('cake_d_c/users', 'Reload'),
                        ['action' => 'u2fAuthenticate'],
                        ['class' => 'btn btn-primary']
                        )?></p>
                </fieldset>
                <?= $this->Form->hidden('authenticateResponse', ['secure' => false, 'id' => 'authenticateResponse'])?>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
<?php
$req = json_encode($authenticateRequest);
$this->Html->scriptStart(['block' => true]);
?>
    setTimeout(function() {
        var req = <?= $req ?>;
        var appId = req[0].appId;
        var challenge = req[0].challenge;

        u2f.sign(appId, challenge, req, function(data) {
            var targetForm = document.getElementById('u2fAuthenticateFrm');
            var targetInput = document.getElementById('authenticateResponse');
            if(data.errorCode && data.errorCode != 0) {
                alert("<?= __d('cake_d_c/users', 'Yubico key check has failed, please try again')?>");

                return;
            }
            targetInput.value = JSON.stringify(data);
            targetForm.submit();
        });
    }, 1000);
<?php $this->Html->scriptEnd();?>
