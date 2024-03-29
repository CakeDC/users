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

use Cake\Core\Configure;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
// use CakeDC\Users\Controller\Traits\CustomUsersTableTrait;
use CakeDC\Users\Controller\Traits\LinkSocialTrait;
use CakeDC\Users\Controller\Traits\LoginTrait;
use CakeDC\Users\Controller\Traits\OneTimePasswordVerifyTrait;
use CakeDC\Users\Controller\Traits\ProfileTrait;
use CakeDC\Users\Controller\Traits\ReCaptchaTrait;
use CakeDC\Users\Controller\Traits\RegisterTrait;
use CakeDC\Users\Controller\Traits\SimpleCrudTrait;
use CakeDC\Users\Controller\Traits\SocialTrait;
use CakeDC\Users\Controller\Traits\Webauthn2faTrait;

/**
 * Users Controller
 *
 * @property \CakeDC\Users\Model\Table\UsersTable $Users
 * @property \Cake\Controller\Component\FormProtectionComponent|null $FormProtection
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
    use Webauthn2faTrait;

    // use CustomUsersTableTrait;

    /**
     * Initialize
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        if ($this->components()->has('FormProtection')) {
            $this->FormProtection->setConfig(
                'unlockedActions',
                [
                    'login',
                    'webauthn2faRegister',
                    'webauthn2faRegisterOptions',
                    'webauthn2faAuthenticate',
                    'webauthn2faAuthenticateOptions',
                ]
            );
        }
    }

    protected ?Table $_usersTable = null;

    /**
     * Gets the users table instance
     *
     * @return \Cake\ORM\Table
     */
    public function getUsersTable()
    {
        if ($this->_usersTable instanceof Table) {
            return $this->_usersTable;
        }
        $this->_usersTable = TableRegistry::getTableLocator()->get(Configure::read('Users.table'));

        return $this->_usersTable;
    }

    /**
     * Set the users table
     *
     * @param \Cake\ORM\Table $table table
     * @return void
     */
    public function setUsersTable(Table $table)
    {
        $this->_usersTable = $table;
    }
}
