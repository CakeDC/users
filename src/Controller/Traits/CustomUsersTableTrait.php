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

namespace CakeDC\Users\Controller\Traits;

use Cake\Core\Configure;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

/**
 * Customize Users Table
 */
trait CustomUsersTableTrait
{
    /**
     * @var \Cake\ORM\Table|null
     */
    protected ?Table $usersTable = null;

    /**
     * Gets the users table instance
     *
     * @return \Cake\ORM\Table
     */
    public function getUsersTable(): Table
    {
        if ($this->usersTable instanceof Table) {
            return $this->usersTable;
        }
        $this->usersTable = TableRegistry::getTableLocator()->get(Configure::read('Users.table'));

        return $this->usersTable;
    }

    /**
     * Set the users table
     *
     * @param \Cake\ORM\Table $table table
     * @return void
     */
    public function setUsersTable(Table $table): void
    {
        $this->usersTable = $table;
    }
}
