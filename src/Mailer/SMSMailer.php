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
use Cake\Mailer\TransportFactory;
use CakeDC\Users\Utility\UsersUrl;

/**
 * SMS Mailer
 */
class SMSMailer extends Mailer
{
    public function __construct($config = null)
    {
        parent::__construct();
        $smsConfig = Mailer::getConfig(Configure::read('Code2f.config', 'sms'));
        $phonePattern = TransportFactory::get($smsConfig['transport'])->getConfig('phonePattern');
        if (!$phonePattern) {
            throw new \UnexpectedValueException(__d('cake_d_c/users', 'You must define `phonePattern` in your transport ({0}) config.', $config));
        }
        $this->setEmailPattern($phonePattern);
        $this->setProfile($config);
        $this->setEmailFormat('text');
    }

    public function otp(EntityInterface $user, $code)
    {
        $this->setTo($user->phone);
        $this->deliver(__d('cake_d_c/users', Configure::read('Code2f.message'), $code, Configure::read('App.name')));
    }

}
