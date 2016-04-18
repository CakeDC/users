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
use Cake\Network\Exception\NotFoundException;

/**
 * Covers registration features and email token validation
 *
 */
trait SocialTrait
{
    /**
     * Render the social email form
     *
     * @throws NotFoundException
     * @return void
     */
    public function socialEmail()
    {
        if (!$this->request->session()->check(Configure::read('Users.Key.Session.social'))) {
            throw new NotFoundException();
        }
        $this->request->session()->delete('Flash.auth');

        if ($this->request->is('post')) {
            $validPost = $this->_validateRegisterPost();
            if (!$validPost) {
                $this->Flash->error(__d('Users', 'The reCaptcha could not be validated'));
                return;
            }
            $user = $this->Auth->identify();
            return $this->_afterIdentifyUser($user, true);
        }
    }
}
