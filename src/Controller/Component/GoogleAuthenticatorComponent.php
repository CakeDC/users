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

namespace CakeDC\Users\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Utility\Security;
use InvalidArgumentException;
use RobThree\Auth\TwoFactorAuth;

/**
 * GoogleAuthenticator Component.
 *
 * @link https://github.com/RobThree/TwoFactorAuth
 */
class GoogleAuthenticatorComponent extends Component
{
    /** @var \RobThree\Auth\TwoFactorAuth $tfa */
    public $tfa;

    /**
     * initialize method
     * @param array $config The config data
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        if (Configure::read('Users.GoogleAuthenticator.login')) {
            $this->tfa = new TwoFactorAuth(
                Configure::read('Users.GoogleAuthenticator.issuer'),
                Configure::read('Users.GoogleAuthenticator.digits'),
                Configure::read('Users.GoogleAuthenticator.period'),
                Configure::read('Users.GoogleAuthenticator.algorithm'),
                Configure::read('Users.GoogleAuthenticator.qrcodeprovider'),
                Configure::read('Users.GoogleAuthenticator.rngprovider')
            );
        }
    }

    /**
     * createSecret
     * @return string base32 shared secret stored in users table
     */
    public function createSecret()
    {
        return $this->tfa->createSecret();
    }

    /**
     * verifyCode
     * Verifying tfa code with shared secret
     * @param string $secret of the user
     * @param string $code from verification form
     * @return bool
     */
    public function verifyCode($secret, $code)
    {
        return $this->tfa->verifyCode($secret, $code);
    }

    /**
     * getQRCodeImageAsDataUri
     * @param string $issuer issuer
     * @param string $secret secret
     * @return string base64 string containing QR code for shared secret
     */
    public function getQRCodeImageAsDataUri($issuer, $secret)
    {
        return $this->tfa->getQRCodeImageAsDataUri($issuer, $secret);
    }
}
