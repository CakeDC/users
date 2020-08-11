<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace TestApp\Mailer;

use Cake\Datasource\EntityInterface;
use CakeDC\Users\Mailer\UsersMailer;

/**
 * Override default mailer class to test customization
 */
class OverrideMailer extends UsersMailer
{
    /**
     * Override the resetPassword email with a custom template and subject
     *
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
