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
<?= __d('cake_d_c/users', "Hi {0}", $user['first_name']) ?>,

<?= __d(
    'cake_d_c/users',
    "Please copy the following address in your web browser to activate your social login {0}",
    $this->Url->build($activationUrl)
) ?>

<?= __d('cake_d_c/users', 'Thank you') ?>,

