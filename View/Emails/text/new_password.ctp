<?php
/**
 * Copyright 2009 - 2018, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2009 - 2018, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

echo __d('users', 'Your password has been reset');
echo __d('users', 'Please login using this password and change your password');
echo "\n";
__d('users', 'Your new password is: %s', $userData[$model]['new_password']);