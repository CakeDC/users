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

namespace CakeDC\Users\Controller\Traits;

use Cake\Core\Configure;

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
        $recaptcha = $this->_getReCaptchaInstance();
        if (!empty($recaptcha)) {
            $response = $recaptcha->verify($recaptchaResponse, $clientIp);
            return $response->isSuccess();
        }
        return false;
    }

    /**
     * Create reCaptcha instance if enabled in configuration
     *
     * @return ReCaptcha
     */
    protected function _getReCaptchaInstance()
    {
        $reCaptchaSecret = Configure::read('Users.reCaptcha.secret');
        if (!empty($reCaptchaSecret)) {
            return new \ReCaptcha\ReCaptcha($reCaptchaSecret);
        }
        return null;
    }
}
