<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Controller;

use App\Controller\AppController as BaseController;
use Cake\Core\Configure;

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
        if (!$this->shouldSkipFormProtection()) {
            $this->loadComponent('FormProtection');
        }
        if ($this->request->getParam('_csrfToken') === false) {
            $this->loadComponent('Csrf');
        }
        $this->loadComponent('CakeDC/Users.Setup');
        if (Configure::read('Users.Ajax.enabled')) {
            $this->loadComponent('CakeDC/Users.AjaxResponse');
        }
    }

    /**
     * Skip FormProtection only for JSON-negotiated ajax requests (field-locking is
     * meaningless without a server-rendered form). CSRF protection still applies.
     *
     * @return bool
     */
    protected function shouldSkipFormProtection(): bool
    {
        return Configure::read('Users.Ajax.enabled')
            && Configure::read('Users.Ajax.skipFormProtectionForJson')
            && $this->request->is('json');
    }
}
