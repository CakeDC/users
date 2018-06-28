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

namespace CakeDC\Users\Controller\Traits;

use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use CakeDC\Users\Middleware\SocialAuthMiddleware;

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
        if ($this->request->is('post')) {
            $status = $this->request->getAttribute('socialAuthStatus');
            if ($status === SocialAuthMiddleware::AUTH_ERROR_INVALID_RECAPTCHA) {
                $this->Flash->error(__d('CakeDC/Users', 'The reCaptcha could not be validated'));

                return;
            }

            $result = $this->request->getAttribute('authentication')->getResult();
            if ($result->isValid()) {
                $user = $this->request->getAttribute('identity')->getOriginalData();

                return $this->_afterIdentifyUser($user);
            }
        }
    }
}
