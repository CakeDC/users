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

    /**
     * Get the two factor authentication checker
     *
     * @return TwoFactorAuthenticationCheckerInterface
     */
    public function getChecker()
    {
        $className = Configure::read('GoogleAuthenticator.checker');

        $interfaces = class_implements($className);
        $required = 'CakeDC\Users\Auth\TwoFactorAuthenticationCheckerInterface';

        if (in_array($required, $interfaces)) {
            return new $className();
        }

        throw new InvalidArgumentException("Invalid config for 'GoogleAuthenticator.checker', '$className' does not implement '$required'");
    }
}
