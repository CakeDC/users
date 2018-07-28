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

use App\Controller\AppController as BaseController;
use Cake\Core\Configure;

/**
 * AppController for Users Plugin
 *
 */
class AppController extends BaseController
{
    protected $_defaultAuthorizationConfig = [
        'skipAuthorization' => [
            'validateAccount',
            // LoginTrait
            'socialLogin',
            'login',
            'logout',
            'socialEmail',
            'verify',
            // RegisterTrait
            'register',
            'validateEmail',
            // PasswordManagementTrait used in RegisterTrait
            'changePassword',
            'resetPassword',
            'requestResetPassword',
            // UserValidationTrait used in PasswordManagementTrait
            'resendTokenValidation',
        ]
    ];

    /**
     * Initialize
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Security');
        if ($this->request->getParam('_csrfToken') === false) {
            $this->loadComponent('Csrf');
        }
        $this->loadComponent('Authentication.Authentication', Configure::read('Auth.AuthenticationComponent'));

        if (Configure::read('Auth.AuthorizationComponent.enable') !== false) {
            $config = (array)Configure::read('Auth.AuthorizationComponent') + $this->_defaultAuthorizationConfig;

            $this->loadComponent('Authorization.Authorization', $config);
        }

        if (Configure::read('Users.GoogleAuthenticator.login') !== false) {
            $this->loadComponent('CakeDC/Users.GoogleAuthenticator');
        }
    }
}
