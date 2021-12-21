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

namespace CakeDC\Users\Controller;

use CakeDC\Users\Controller\Traits\LinkSocialTrait;
use CakeDC\Users\Controller\Traits\LoginTrait;
use CakeDC\Users\Controller\Traits\OneTimePasswordVerifyTrait;
use CakeDC\Users\Controller\Traits\ProfileTrait;
use CakeDC\Users\Controller\Traits\ReCaptchaTrait;
use CakeDC\Users\Controller\Traits\RegisterTrait;
use CakeDC\Users\Controller\Traits\SimpleCrudTrait;
use CakeDC\Users\Controller\Traits\SocialTrait;
use CakeDC\Users\Controller\Traits\U2fTrait;
use CakeDC\Users\Controller\Traits\Webauthn2faTrait;

/**
 * Users Controller
 *
 * @property \CakeDC\Users\Model\Table\UsersTable $Users
 * @property \Cake\Controller\Component\SecurityComponent $Security
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
    use U2fTrait;
    use Webauthn2faTrait;

    /**
     * Initialize
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        if ($this->components()->has('Security')) {
            $this->Security->setConfig(
                'unlockedActions',
                [
                    'login',
                    'u2fRegister',
                    'u2fRegisterFinish',
                    'u2fAuthenticate',
                    'u2fAuthenticateFinish',
                    'webauthn2faRegister',
                    'webauthn2faRegisterOptions',
                    'webauthn2faAuthenticate',
                    'webauthn2faAuthenticateOptions',
                ]
            );
        }
    }
}
