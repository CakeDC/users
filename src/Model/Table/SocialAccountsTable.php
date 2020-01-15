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

namespace CakeDC\Users\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SocialAccounts Model
 *
 * @mixin \CakeDC\Users\Model\Behavior\SocialAccountBehavior
 */
class SocialAccountsTable extends Table
{
    /**
     * Constants
     */
    public const PROVIDER_TWITTER = 'Twitter';
    public const PROVIDER_FACEBOOK = 'Facebook';
    public const PROVIDER_INSTAGRAM = 'Instagram';
    public const PROVIDER_LINKEDIN = 'LinkedIn';

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
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
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->add('id', 'valid', ['rule' => 'uuid'])
            ->allowEmptyString('id', null, 'create');

        $validator
            ->requirePresence('provider', 'create')
            ->notEmptyString('provider');

        $validator
            ->allowEmptyString('username');

        $validator
            ->requirePresence('reference', 'create')
            ->notEmptyString('reference');

        $validator
            ->requirePresence('link', 'create')
            ->notEmptyString('reference');

        $validator
            ->allowEmptyString('avatar');

        $validator
            ->allowEmptyString('description');

        $validator
            ->requirePresence('token', 'create')
            ->notEmptyString('token');

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
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }

    /**
     * Finder for active social accounts
     *
     * @param \Cake\ORM\Query $query query
     * @return \Cake\ORM\Query
     */
    public function findActive(Query $query)
    {
        return $query->where([
            $this->aliasField('active') => true,
        ]);
    }
}
