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

$activationUrl = [
    '_full' => true,
    'plugin' => 'CakeDC/Users',
    'controller' => 'Users',
    'action' => 'validateEmail',
    isset($token) ? $token : ''
];
?>
<?= __d('cake_d_c/users', "Hi {0}", isset($first_name) ? $first_name : '') ?>,

<?= __d(
    'cake_d_c/users',
    "Please copy the following address in your web browser {0}",
    $this->Url->build($activationUrl)
) ?>

<?= __d('cake_d_c/users', 'Thank you') ?>,

