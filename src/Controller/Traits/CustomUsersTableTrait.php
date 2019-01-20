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

namespace CakeDC\Users\Controller\Traits;

use Cake\Core\Configure;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

/**
 * Customize Users Table
 *
 */
trait CustomUsersTableTrait
{
    protected $_usersTable = null;

    /**
     * Gets the users table instance
     *
     * @return Table
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
     * @param Table $table table
     * @return void
     */
    public function setUsersTable(Table $table)
    {
        $this->_usersTable = $table;
    }
}
