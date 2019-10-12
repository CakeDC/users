<?php
/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SocialAccounts Model
 */
class SocialAccountsTable extends Table
{

    /**
     * Constants
     */
    const PROVIDER_TWITTER = 'Twitter';
    const PROVIDER_FACEBOOK = 'Facebook';
    const PROVIDER_INSTAGRAM = 'Instagram';
    const PROVIDER_LINKEDIN = 'LinkedIn';

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('social_accounts');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->addBehavior('CakeDC/Users.SocialAccount');
    }

    /**
     * Default validation rules.
     *
     * @param Validator $validator Validator instance.
     * @return Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'uuid'])
            ->allowEmptyString('id', null, 'create');

        $validator
            ->requirePresence('provider', 'create')
            ->notBlank('provider');

        $validator
            ->allowEmptyString('username');

        $validator
            ->requirePresence('reference', 'create')
            ->allowEmptyString('reference');

        $validator
            ->requirePresence('link', 'create')
            ->allowEmptyString('reference');

        $validator
            ->allowEmptyString('avatar');

        $validator
            ->allowEmptyString('description');

        $validator
            ->requirePresence('token', 'create')
            ->notBlank('token');

        $validator
            ->allowEmptyString('token_secret');

        $validator
            ->add('token_expires', 'valid', ['rule' => 'datetime'])
            ->allowEmptyString('token_expires');

        $validator
            ->add('active', 'valid', ['rule' => 'boolean'])
            ->requirePresence('active', 'create')
            ->notBlank('active');

        $validator
            ->requirePresence('data', 'create')
            ->notBlank('data');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param RulesChecker $rules The rules object to be modified.
     * @return RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }

    /**
     * Finder for active social accounts
     *
     * @param Query $query query
     * @return \Cake\ORM\Query
     */
    public function findActive(Query $query)
    {
        return $query->where([
            $this->aliasField('active') => true
        ]);
    }
}
