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
<p>
    <?= __d('cake_d_c/users', "Hi {0}", $user->first_name ?? ($user->username ?? '')) ?>,
</p>
<p>
    <strong><?= __d('cake_d_c/users', 'Your one-time login token is: {0}. Please click {1} to login.', $token, $this->Html->link(__d('cake_d_c/users', 'here'), $loginLink)) ?></strong>
</p>
<p>
<?= __d(
    'cake_d_c/users',
    "If the link is not correctly displayed, please copy the following address in your web browser {0}",
    $this->Url->build($loginLink)
) ?>
</p>
<p>
    <?= __d('cake_d_c/users', 'Thank you') ?>,
</p>
