<?php
/**
 * Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Controller\Traits;

use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;

/**
 * Covers registration features and email token validation
 *
 * @property \Cake\Http\ServerRequest $request
 */
trait SocialTrait
{
    /**
     * Render the social email form
     *
     * @throws NotFoundException
     * @return mixed
     */
    public function socialEmail()
    {
        $config = Configure::read('Auth.SocialLoginFailure');
        /**
         * @var \CakeDC\Users\Controller\Component\LoginComponent $Login
         */
        $Login = $this->loadComponent($config['component'], $config);

        return $Login->handleLogin(true, false);
    }
}
