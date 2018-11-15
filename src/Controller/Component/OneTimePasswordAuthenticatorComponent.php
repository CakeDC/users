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
use RobThree\Auth\TwoFactorAuth;

/**
 * OneTimePasswordAuthenticator Component.
 *
 * @link https://github.com/RobThree/TwoFactorAuth
 */
class OneTimePasswordAuthenticatorComponent extends Component
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

        if (Configure::read('Users.OneTimePasswordAuthenticator.login')) {
            $this->tfa = new TwoFactorAuth(
                Configure::read('Users.OneTimePasswordAuthenticator.issuer'),
                Configure::read('Users.OneTimePasswordAuthenticator.digits'),
                Configure::read('Users.OneTimePasswordAuthenticator.period'),
                Configure::read('Users.OneTimePasswordAuthenticator.algorithm'),
                Configure::read('Users.OneTimePasswordAuthenticator.qrcodeprovider'),
                Configure::read('Users.OneTimePasswordAuthenticator.rngprovider')
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
