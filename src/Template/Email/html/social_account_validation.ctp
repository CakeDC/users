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
?>

<p>
<?= __d('CakeDC/Users', "Hi {0}", $user['first_name']) ?>,
</p>
<p>
    <strong><?php
    $text = __d('CakeDC/Users', 'Activate your social login here');
    $activationUrl = [
        '_full' => true,
        'plugin' => 'CakeDC/Users',
        'controller' => 'SocialAccounts',
        'action' => 'validateAccount',
        $socialAccount['provider'],
        $socialAccount['reference'],
        $socialAccount['token'],
    ];
    echo $this->Html->link($text, $activationUrl);
    ?></strong>
</p>
<p>
    <?= __d('CakeDC/Users', "If the link is not correcly displayed, please copy the following address in your web browser {0}", $this->Url->build($activationUrl)) ?>
</p>
<p>
    <?= __d('CakeDC/Users', 'Thank you') ?>,
</p>
