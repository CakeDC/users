<?php
namespace CakeDC\Users\Controller\Component;

use Cake\Core\Plugin;
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
	/** @var RobThree\Auth\TwoFactorAuth $tfa */
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
                Configure::read('Users.GoogleAuthenticator.rngprovider'),
                Configure::read('Users.GoogleAuthenticator.encryptionKey')
            );
        }
	}

    /**
     * createSecret
     * @return base32 shared secret stored in users table
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
     * @return boolean
     */
    public function verifyCode($secret, $code)
    {
        return $this->tfa->verifyCode($secret, $code);
    }

    /**
     * getQRCodeImageAsDataUri
     * @return string base64 string containing QR code for shared secret
     */
    public function getQRCodeImageAsDataUri($issuer, $secret)
    {
        return $this->tfa->getQRCodeImageAsDataUri($issuer, $secret);
    }
}

