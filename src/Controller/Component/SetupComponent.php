<?php
/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace CakeDC\Users\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Utility\Hash;

class SetupComponent extends Component
{
    /**
     * Initialize
     *
     * @param array $config component configuration
     * @throws \Exception
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->loadAuthComponents($this->getController());
    }

    /**
     * Load all auth components needed: Authentication.Authentication, Authorization.Authorization and CakeDC/OneTimePasswordAuthenticator
     *
     * @param \Cake\Controller\Controller $controller Target controller
     * @return void
     * @throws \Exception
     */
    protected function loadAuthComponents($controller)
    {
        $authenticationConfig = Configure::read('Auth.AuthenticationComponent');
        if (Hash::get($authenticationConfig, 'load')) {
            unset($authenticationConfig['config']);
            $controller->loadComponent('Authentication.Authentication', $authenticationConfig);
        }

        if (Configure::read('Auth.AuthorizationComponent.enable') !== false) {
            $config = (array)Configure::read('Auth.AuthorizationComponent');
            $controller->loadComponent('Authorization.Authorization', $config);
        }

        if (Configure::read('OneTimePasswordAuthenticator.login') !== false) {
            $controller->loadComponent('CakeDC/Auth.OneTimePasswordAuthenticator');
        }
    }
}
