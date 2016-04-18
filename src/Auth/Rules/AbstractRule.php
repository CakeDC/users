<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (+1 702 425 5085) (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (+1 702 425 5085) (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace CakeDC\Users\Auth\Rules;

use Cake\Network\Request;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use OutOfBoundsException;

/**
 * Class AbstractRule
 * @package CakeDC\Users\Auth\Rules
 */
abstract class AbstractRule implements Rule
{
    use \Cake\Core\InstanceConfigTrait;
    use \Cake\Datasource\ModelAwareTrait;
    use \Cake\ORM\Locator\LocatorAwareTrait;

    /**
     * @var array default config
     */
    protected $_defaultConfig = [];

    /**
     * AbstractRule constructor.
     * @param array $config Rule config
     */
    public function __construct($config = [])
    {
        $this->config($config);
    }

    /**
     * Get a table from the alias, table object or inspecting the request for a default table
     *
     * @param Request $request request
     * @param mixed $table table
     * @return \Cake\ORM\Table
     * @throw OutOfBoundsException if table alias is empty
     */
    protected function _getTable(Request $request, $table = null)
    {
        if (empty($table)) {
            return $this->_getTableFromRequest($request);
        }
        if ($table instanceof Table) {
            return $table;
        }
        return TableRegistry::get($table);
    }

    /**
     * Inspect the request and try to retrieve a table based on the current controller
     *
     * @param Request $request request
     * @return Table
     * @throws OutOfBoundsException if table alias can't be extracted from request
     */
    protected function _getTableFromRequest(Request $request)
    {
        $plugin = Hash::get($request->params, 'plugin');
        $controller = Hash::get($request->params, 'controller');
        $modelClass = ($plugin ? $plugin . '.' : '') . $controller;

        $this->modelFactory('Table', [$this->tableLocator(), 'get']);
        if (empty($modelClass)) {
            throw new OutOfBoundsException(__d('Users', 'Table alias is empty, please define a table alias, we could not extract a default table from the request'));
        }
        return $this->loadModel($modelClass);
    }

    /**
     * Check the current entity is owned by the logged in user
     *
     * @param array $user Auth array with the logged in data
     * @param string $role role of the user
     * @param Request $request current request, used to get a default table if not provided
     * @return bool
     * @throws OutOfBoundsException if table is not found or it doesn't have the expected fields
     */
    abstract public function allowed(array $user, $role, Request $request);
}
