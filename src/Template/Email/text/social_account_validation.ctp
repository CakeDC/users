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
        'plugin' => 'CakeDC/Users',
        'controller' => 'SocialAccounts',
        'action' => 'validateAccount',
        $socialAccount['provider'],
        $socialAccount['reference'],
        $socialAccount['token'],
    ];
?>
<?= __d('CakeDC/Users', "Hi {0}", $user['first_name']) ?>,

<?= __d('CakeDC/Users', "Please copy the following address in your web browser to activate your social login {0}", $this->Url->build($activationUrl)) ?>

<?= __d('CakeDC/Users', 'Thank you') ?>,

