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
     * Validates reCAPTCHA response
     * @param $recaptchaResponse
     * @param $clientIp
     * @return bool
     */
    public function validateReCaptcha($recaptchaResponse, $clientIp)
    {
        $validReCaptcha = true;
        $useReCaptcha = (bool)Configure::read('Users.Registration.reCAPTCHA');
        $reCaptchaSecret = Configure::read('reCAPTCHA.secret');
        if ($useReCaptcha && !empty($reCaptchaSecret)) {
            $recaptcha = new ReCaptcha($reCaptchaSecret);
            $response = $recaptcha->verify($recaptchaResponse, $clientIp);
            $validReCaptcha = $response->isSuccess();
        }
        return $validReCaptcha;
    }
}
