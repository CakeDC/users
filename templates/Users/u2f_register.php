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
                        'action' => 'u2fRegisterFinish',
                        '?' => $this->request->getQueryParams()
                    ],
                    'id' => 'u2fRegisterFrm'
                ]) ?>

                <?= $this->Flash->render('auth') ?>
                <?= $this->Flash->render() ?>
                <fieldset>
                    <h2 class='text-center'><?= __d('cake_d_c/users', 'Registering your yubico key')?> </h2>
                    <h3 class='text-center'><?= __d('cake_d_c/users', 'Please insert and tap your yubico key')?></h3>
                    <p>In order to enable your YubiKey the first step is to perform a registration.</p>
                    <p>When the YubiKey starts blinking, press the golden disc to activate it. Depending on the web browser you might need to confirm the use of extended information from the YubiKey.</p>
                    <p class="text-center"><?= $this->Html->link(
                        __d('cake_d_c/users', 'Reload'),
                        ['action' => 'u2fRegister'],
                        ['class' => 'btn btn-primary']
                        )?></p>
                </fieldset>
                <?= $this->Form->hidden('registerResponse', ['secure' => false, 'id' => 'registerResponse'])?>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
<?php
$req = json_encode([
    'appId' => $registerRequest->appId,
    'version' => $registerRequest->version,
    'challenge' => $registerRequest->challenge,
    'attestation' => 'direct'
]);
$this->Html->scriptStart(['block' => true]);
?>
setTimeout(function() {
    var req = <?= $req ?>;
    var appId = req.appId;
    var registerRequests = [req];
    u2f.register(appId, registerRequests, [], function(data) {
        var targetForm = document.getElementById('u2fRegisterFrm');
        var targetInput = document.getElementById('registerResponse');

        if(data.errorCode && data.errorCode != 0) {
            alert("<?= __d('cake_d_c/users', 'Yubico key check has failed, please try again')?>");

            return;
        }
        targetInput.value = JSON.stringify(data);
        targetForm.submit();
    });
}, 1000);
<?php $this->Html->scriptEnd();?>
