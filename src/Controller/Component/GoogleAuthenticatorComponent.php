<?php
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
 * Saves a cookie to keep the user logged into the application even when the session expires
 *
 * @link http://book.cakephp.org/3.0/en/controllers/components/cookie.html
 */
class GoogleAuthenticatorComponent extends Component
{
	public $tfa;

	public function initialize(array $config)
	{
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

    public function verifyCode($secret, $code)
    {
        return $this->tfa->verifyCode($secret, $code);
    }

    public function getQRCodeImageAsDataUri($issuer, $secret)
    {
        return $this->tfa->getQRCodeImageAsDataUri($issuer, $secret);
    }
}

