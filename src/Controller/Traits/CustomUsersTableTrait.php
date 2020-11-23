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
    protected $_usersTable = null;

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
