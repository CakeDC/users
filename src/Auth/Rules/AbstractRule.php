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

use Cake\Core\InstanceConfigTrait;
use Cake\Datasource\ModelAwareTrait;
use Cake\Http\ServerRequest;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use OutOfBoundsException;

/**
 * Class AbstractRule
 * @package CakeDC\Users\Auth\Rules
 */
abstract class AbstractRule implements Rule
{
    use InstanceConfigTrait;
    use LocatorAwareTrait;
    use ModelAwareTrait;

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
        $this->setConfig($config);
    }

    /**
     * Get a table from the alias, table object or inspecting the request for a default table
     *
     * @param \Cake\Http\ServerRequest $request request
     * @param mixed $table table
     * @return \Cake\ORM\Table
     * @throws \OutOfBoundsException if table alias is empty
     */
    protected function _getTable(ServerRequest $request, $table = null)
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
     * @param \Cake\Http\ServerRequest $request request
     * @return \Cake\Datasource\RepositoryInterface
     * @throws \OutOfBoundsException if table alias can't be extracted from request
     */
    protected function _getTableFromRequest(ServerRequest $request)
    {
        $plugin = $request->getParam('plugin');
        $controller = $request->getParams('controller');
        $modelClass = ($plugin ? $plugin . '.' : '') . $controller;

        $this->modelFactory('Table', [$this->tableLocator(), 'get']);
        if (empty($modelClass)) {
            $msg = __d('CakeDC/Users', 'Missing Table alias, we could not extract a default table from the request');
            throw new OutOfBoundsException($msg);
        }

        return $this->loadModel($modelClass);
    }

    /**
     * Check the current entity is owned by the logged in user
     *
     * @param array $user Auth array with the logged in data
     * @param string $role role of the user
     * @param \Cake\Http\ServerRequest $request current request, used to get a default table if not provided
     * @return bool
     * @throws \OutOfBoundsException if table is not found or it doesn't have the expected fields
     */
    abstract public function allowed(array $user, $role, ServerRequest $request);
}
