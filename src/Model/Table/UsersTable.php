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

use Cake\Database\Schema\TableSchema;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Users Model
 */
class UsersTable extends Table
{

    /**
     * Role Constants
     */
    const ROLE_USER = 'user';
    const ROLE_ADMIN = 'admin';

    /**
     * Flag to set email check in buildRules or not
     *
     * @var bool
     */
    public $isValidateEmail = false;

    /**
     * Field additional_data is json
     *
     * @param \Cake\Database\Schema\TableSchema $schema The table definition fetched from database.
     * @return \Cake\Database\Schema\TableSchema the altered schema
     */
    protected function _initializeSchema(TableSchema $schema)
    {
        $schema->setColumnType('additional_data', 'json');

        return parent::_initializeSchema($schema);
    }

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('username');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->addBehavior('CakeDC/Users.Register');
        $this->addBehavior('CakeDC/Users.Password');
        $this->addBehavior('CakeDC/Users.Social');
        $this->addBehavior('CakeDC/Users.LinkSocial');
        $this->addBehavior('CakeDC/Users.AuthFinder');
        $this->hasMany('SocialAccounts', [
            'foreignKey' => 'user_id',
            'className' => 'CakeDC/Users.SocialAccounts'
        ]);
    }

    /**
     * Adds some rules for password confirm
     * @param Validator $validator Cake validator object.
     * @return Validator
     */
    public function validationPasswordConfirm(Validator $validator)
    {
        $validator
            ->requirePresence('password_confirm', 'create')
            ->notBlank('password_confirm');

        $validator
            ->requirePresence('password', 'create')
            ->notBlank('password')
            ->add('password', [
                'password_confirm_check' => [
                    'rule' => ['compareWith', 'password_confirm'],
                    'message' => __d('cake_d_c/users', 'Your password does not match your confirm password. Please try again'),
                    'allowEmpty' => false
                ]]);

        return $validator;
    }

    /**
     * Adds rules for current password
     *
     * @param Validator $validator Cake validator object.
     * @return Validator
     */
    public function validationCurrentPassword(Validator $validator)
    {
        $validator
            ->notBlank('current_password');

        return $validator;
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
            ->allowEmptyString('id', null, 'create');

        $validator
            ->requirePresence('username', 'create')
            ->notBlank('username');

        $validator
            ->requirePresence('password', 'create')
            ->notBlank('password');

        $validator
            ->allowEmptyString('first_name');

        $validator
            ->allowEmptyString('last_name');

        $validator
            ->allowEmptyString('token');

        $validator
            ->add('token_expires', 'valid', ['rule' => 'datetime'])
            ->allowEmptyDateTime('token_expires');

        $validator
            ->allowEmptyString('api_token');

        $validator
            ->add('activation_date', 'valid', ['rule' => 'datetime'])
            ->allowEmptyDateTime('activation_date');

        $validator
            ->add('tos_date', 'valid', ['rule' => 'datetime'])
            ->allowEmptyDateTime('tos_date');

        return $validator;
    }

    /**
     * Wrapper for all validation rules for register
     * @param Validator $validator Cake validator object.
     *
     * @return Validator
     */
    public function validationRegister(Validator $validator)
    {
        $validator = $this->validationDefault($validator);
        $validator = $this->validationPasswordConfirm($validator);

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
        $rules->add($rules->isUnique(['username']), '_isUnique', [
            'errorField' => 'username',
            'message' => __d('cake_d_c/users', 'Username already exists')
        ]);

        if ($this->isValidateEmail) {
            $rules->add($rules->isUnique(['email']), '_isUnique', [
                'errorField' => 'email',
                'message' => __d('cake_d_c/users', 'Email already exists')
            ]);
        }

        return $rules;
    }
}
