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

namespace CakeDC\Users\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Cake\Utility\Hash;

/**
 * Implement finders used by Auth
 */
class AuthFinderBehavior extends Behavior
{
    /**
     * Custom finder to filter active users
     *
     * @param Query $query Query object to modify
     * @param array $options Query options
     * @return Query
     */
    public function findActive(Query $query, array $options = [])
    {
        $query->where([$this->getTable()->aliasField('active') => 1]);

        return $query;
    }

    /**
     * Custom finder to log in users
     *
     * @param Query $query Query object to modify
     * @param array $options Query options
     * @return Query
     * @throws \BadMethodCallException
     */
    public function findAuth(Query $query, array $options = [])
    {
        $identifier = Hash::get($options, 'username');
        if (empty($identifier)) {
            throw new \BadMethodCallException(__d('CakeDC/Users', 'Missing \'username\' in options data'));
        }

        $query
            ->orWhere([$this->getTable()->aliasField('email') => $identifier])
            ->find('active', $options);

        return $query;
    }
}
