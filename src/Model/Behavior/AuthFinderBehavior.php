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

namespace CakeDC\Users\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\Query;

/**
 * Implement finders used by Auth
 */
class AuthFinderBehavior extends Behavior
{
    /**
     * Custom finder to filter active users
     *
     * @param \Cake\ORM\Query $query Query object to modify
     * @return \Cake\ORM\Query
     */
    public function findActive(Query $query)
    {
        $query->where([$this->_table->aliasField('active') => 1]);

        return $query;
    }

    /**
     * Custom finder to log in users
     *
     * @param \Cake\ORM\Query $query Query object to modify
     * @param array $options Query options
     * @return \Cake\ORM\Query
     * @throws \BadMethodCallException
     */
    public function findAuth(Query $query, array $options = [])
    {
        $identifier = $options['username'] ?? null;
        if (empty($identifier)) {
            throw new \BadMethodCallException(__d('cake_d_c/users', 'Missing \'username\' in options data'));
        }
        $where = $query->clause('where') ?: [];
        $query
            ->where(function ($exp) use ($identifier, $where) {
                $or = $exp->or([$this->_table->aliasField('email') => $identifier]);

                return $or->add($where);
            }, [], true)
            ->find('active', $options);

        return $query;
    }
}
