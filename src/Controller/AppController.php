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

use App\Controller\AppController as BaseController;

/**
 * AppController for Users Plugin
 */
class AppController extends BaseController
{
    /**
     * Initialize
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Security');
        if ($this->request->getParam('_csrfToken') === false) {
            $this->loadComponent('Csrf');
        }
        $this->loadComponent('CakeDC/Users.Setup');
    }
}
