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

use Cake\Core\Exception\Exception;
use Cake\Network\Request;
use Cake\Utility\Hash;
use OutOfBoundsException;

/**
 * Owner rule class, used to match ownership permissions
 */
class Owner extends AbstractRule
{
    protected $_defaultConfig = [
        //field in the owned table matching the user_id
        'ownerForeignKey' => 'user_id',
        /*
         * request key type to retrieve the table id, could be "params", "query", "data" to locate the table id
         * example:
         *   yoursite.com/controller/action/XXX would be
         *     tableKeyType => 'params', 'tableIdParamsKey' => 'pass.0'
         *   yoursite.com/controlerr/action?post_id=XXX would be
         *     tableKeyType => 'query', 'tableIdParamsKey' => 'post_id'
         *   yoursite.com/controller/action [posted form with a field named post_id] would be
         *     tableKeyType => 'data', 'tableIdParamsKey' => 'post_id'
         */
        'tableKeyType' => 'params',
        // request->params key path to retrieve the owned table id
        'tableIdParamsKey' => 'pass.0',
        /*
         * define table to use or pick it from controller name defaults if null
         * if null, table used will be based on current controller's default table
         * if string, TableRegistry::get will be used
         * if Table, the table object will be used
         */
        'table' => null,
        /*
         * define the table id to be used to match the row id, this is useful when checking belongsToMany associations
         * Example: If checking ownership in a PostsUsers table, we should use 'id' => 'post_id'
         * If value is null, we'll use the $table->primaryKey()
         */
        'id' => null,
        'conditions' => [],
    ];

    /**
     * {@inheritdoc}
     */
    public function allowed(array $user, $role, Request $request)
    {
        $table = $this->_getTable($request, $this->config('table'));
        //retrieve table id from request
        $id = Hash::get($request->{$this->config('tableKeyType')}, $this->config('tableIdParamsKey'));
        $userId = Hash::get($user, 'id');

        try {
            if (!$table->hasField($this->config('ownerForeignKey'))) {
                throw new OutOfBoundsException(__d('Users', 'Missing column {0} in table {1} while checking ownership permissions for user {2}', $this->config('ownerForeignKey'), $table->alias(), $userId));
            }
        } catch (Exception $ex) {
            throw new OutOfBoundsException(__d('Users', 'Missing column {0} in table {1} while checking ownership permissions for user {2}', $this->config('ownerForeignKey'), $table->alias(), $userId));
        }
        $idColumn = $this->config('id');
        if (empty($idColumn)) {
            $idColumn = $table->primaryKey();
        }
        $conditions = array_merge([
            $idColumn => $id,
            $this->config('ownerForeignKey') => $userId
        ], $this->config('conditions'));

        return $table->exists($conditions);
    }
}
