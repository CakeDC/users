<?php
/**
 * Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\App\Mailer;

use CakeDC\Users\Mailer\UsersMailer;
use Cake\Datasource\EntityInterface;

/**
 * Override default mailer class to test customization
 *
 */
class OverrideMailer extends UsersMailer
{
    /**
     * Override the resetPassword email with a custom template and subject
     * @param EntityInterface $user
     * @return array|void
     */
    public function resetPassword(EntityInterface $user)
    {
        parent::resetPassword($user);
        $this->setSubject('This is the new subject');
        $this->setTemplate('custom-template-in-app-namespace');
    }
}
