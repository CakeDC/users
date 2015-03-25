<?php
/**
 * Copyright 2010 - 2014, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2014, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

echo __d('users', 'A request to reset your password was sent. To change your password click the link below.');
echo "\n";
echo Router::url(array('admin' => false, 'plugin' => 'users', 'controller' => 'users', 'action' => 'reset_password', $token), true);
