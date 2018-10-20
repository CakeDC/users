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

namespace CakeDC\Users\Controller;

use Cake\Core\Configure;
use CakeDC\Users\Controller\Traits\GoogleVerifyTrait;
use CakeDC\Users\Controller\Traits\LinkSocialTrait;
use CakeDC\Users\Controller\Traits\LoginTrait;
use CakeDC\Users\Controller\Traits\ProfileTrait;
use CakeDC\Users\Controller\Traits\ReCaptchaTrait;
use CakeDC\Users\Controller\Traits\RegisterTrait;
use CakeDC\Users\Controller\Traits\SimpleCrudTrait;
use CakeDC\Users\Controller\Traits\SocialTrait;
use CakeDC\Users\Model\Table\UsersTable;

/**
 * Users Controller
 *
 * @property UsersTable $Users
 */
class UsersController extends AppController
{
    use GoogleVerifyTrait;
    use LinkSocialTrait;
    use LoginTrait;
    use ProfileTrait;
    use ReCaptchaTrait;
    use RegisterTrait;
    use SimpleCrudTrait;
    use SocialTrait;

    /**
     * Initialize
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadAuthComponents();
    }

    /**
     * Load all auth components needed: Authentication.Authentication, Authorization.Authorization and CakeDC/Users.GoogleAuthenticator
     *
     * @return void
     */
    protected function loadAuthComponents()
    {
        $this->loadComponent('Authentication.Authentication', Configure::read('Auth.AuthenticationComponent'));

        if (Configure::read('Auth.AuthorizationComponent.enable') !== false) {
            $config = (array)Configure::read('Auth.AuthorizationComponent');
            $this->loadComponent('Authorization.Authorization', $config);
        }

        if (Configure::read('Users.GoogleAuthenticator.login') !== false) {
            $this->loadComponent('CakeDC/Users.GoogleAuthenticator');
        }
    }
}
