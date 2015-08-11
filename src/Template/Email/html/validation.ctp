<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

$activationUrl = [
    '_full' => true,
    'plugin' => 'Users',
    'controller' => 'Users',
    'action' => 'validateEmail',
    isset($token) ? $token : ''
];
?>
<p>
<?= __d('Users', "Hi {0}", isset($first_name)? $first_name : '') ?>,
</p>
<p>
    <strong><?= $this->Html->link(__d('Users', 'Activate your account here'), $activationUrl) ?></strong>
</p>
<p>
    <?= __d('Users', "If the link is not correcly displayed, please copy the following address in your web browser {0}", $this->Url->build($activationUrl)) ?>
</p>
<p>
    <?= __d('Users', 'Thank you') ?>,
</p>
