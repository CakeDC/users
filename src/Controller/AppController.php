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

namespace Users\Controller;

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
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Security');
        $this->loadComponent('Csrf');
        $this->loadComponent('Users.UsersAuth');
    }
}
