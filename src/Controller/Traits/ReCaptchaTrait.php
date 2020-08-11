<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Controller\Traits;

use Cake\Core\Configure;

/**
 * Covers registration features and email token validation
 *
 * @property \Cake\Http\ServerRequest $request
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
     * @return \ReCaptcha\ReCaptcha|null
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
