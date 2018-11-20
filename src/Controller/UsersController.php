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

namespace CakeDC\Users\Controller;

use CakeDC\Users\Controller\Traits\LinkSocialTrait;
use CakeDC\Users\Controller\Traits\LoginTrait;
use CakeDC\Users\Controller\Traits\OneTimePasswordVerifyTrait;
use CakeDC\Users\Controller\Traits\ProfileTrait;
use CakeDC\Users\Controller\Traits\ReCaptchaTrait;
use CakeDC\Users\Controller\Traits\RegisterTrait;
use CakeDC\Users\Controller\Traits\SimpleCrudTrait;
use CakeDC\Users\Controller\Traits\SocialTrait;
use CakeDC\Users\Model\Table\UsersTable;
use Cake\Core\Configure;
use Cake\Utility\Hash;

/**
 * Users Controller
 *
 * @property UsersTable $Users
 */
class UsersController extends AppController
{
    use LinkSocialTrait;
    use LoginTrait;
    use OneTimePasswordVerifyTrait;
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
     * Load all auth components needed: Authentication.Authentication, Authorization.Authorization and CakeDC/OneTimePasswordAuthenticator
     *
     * @return void
     */
    protected function loadAuthComponents()
    {
        $authenticationConfig = Configure::read('Auth.AuthenticationComponent');
        if (Hash::get($authenticationConfig, 'load')) {
            unset($authenticationConfig['config']);
            $this->loadComponent('Authentication.Authentication', $authenticationConfig);
        }

        if (Configure::read('Auth.AuthorizationComponent.enable') !== false) {
            $config = (array)Configure::read('Auth.AuthorizationComponent');
            $this->loadComponent('Authorization.Authorization', $config);
        }

        if (Configure::read('OneTimePasswordAuthenticator.login') !== false) {
            $this->loadComponent('CakeDC/Auth.OneTimePasswordAuthenticator');
        }
    }
}
