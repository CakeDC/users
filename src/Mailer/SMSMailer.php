<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2022, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2022, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace CakeDC\Users\Mailer;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Mailer\Mailer;
use Cake\Mailer\Message;
use CakeDC\Users\Utility\UsersUrl;

/**
 * SMS Mailer
 */
class SMSMailer extends Mailer
{
    public function __construct($config = null)
    {
        parent::__construct();
        $this->setEmailPattern('/^\+[1-9]\d{1,14}$/m');
        $this->setProfile($config);
        $this->setEmailFormat('text');
    }

    public function otp(EntityInterface $user, $code)
    {
        $this->setTo($user->phone);
        $this->deliver(__(Configure::read('Code2f.message'), $code, Configure::read('App.name')));
    }

}
