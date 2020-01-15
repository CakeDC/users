<?php
/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

?>
<p>
    <?= __d('cake_d_c/users', "Hi {0}", isset($first_name) ? $first_name : '') ?>,
</p>
<p>
    <strong><?= $this->Html->link(__d('cake_d_c/users', 'Reset your password here'), $activationUrl) ?></strong>
</p>
<p>
<?= __d(
    'cake_d_c/users',
    "If the link is not correctly displayed, please copy the following address in your web browser {0}",
    $this->Url->build($activationUrl)
) ?>
</p>
<p>
    <?= __d('cake_d_c/users', 'Thank you') ?>,
</p>
