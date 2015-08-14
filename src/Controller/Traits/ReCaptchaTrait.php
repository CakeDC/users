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

namespace Users\Controller\Traits;

use Cake\Core\Configure;
use ReCaptcha\ReCaptcha;

/**
 * Covers registration features and email token validation
 *
 */
trait ReCaptchaTrait
{

    /**
     * Validates reCaptcha response
     *
     * @param string $recaptchaResponse response
     * @param string $clientIp client ip
     * @return bool
     */
    public function validateReCaptcha($recaptchaResponse, $clientIp)
    {
        $validReCaptcha = true;
        $recaptcha = $this->_getReCaptchaInstance();
        if (!empty($recaptcha)) {
            $response = $recaptcha->verify($recaptchaResponse, $clientIp);
            $validReCaptcha = $response->isSuccess();
        }
        return $validReCaptcha;
    }

    /**
     * Create reCaptcha instance if enabled in configuration
     *
     * @return ReCaptcha
     */
    protected function _getReCaptchaInstance()
    {
        $useReCaptcha = (bool)Configure::read('Users.Registration.reCaptcha');
        $reCaptchaSecret = Configure::read('reCaptcha.secret');
        if ($useReCaptcha && !empty($reCaptchaSecret)) {
            return new ReCaptcha($reCaptchaSecret);
        }
        return null;
    }
}
