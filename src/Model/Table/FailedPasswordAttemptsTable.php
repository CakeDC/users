<?php
declare(strict_types=1);

namespace CakeDC\Users\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * FailedPasswordAttempts Model
 *
 * @property \CakeDC\Users\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @method \CakeDC\Users\Model\Entity\FailedPasswordAttempt newEmptyEntity()
 * @method \CakeDC\Users\Model\Entity\FailedPasswordAttempt newEntity(array $data, array $options = [])
 * @method array<\CakeDC\Users\Model\Entity\FailedPasswordAttempt> newEntities(array $data, array $options = [])
 * @method \CakeDC\Users\Model\Entity\FailedPasswordAttempt get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \CakeDC\Users\Model\Entity\FailedPasswordAttempt findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \CakeDC\Users\Model\Entity\FailedPasswordAttempt patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\CakeDC\Users\Model\Entity\FailedPasswordAttempt> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \CakeDC\Users\Model\Entity\FailedPasswordAttempt|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \CakeDC\Users\Model\Entity\FailedPasswordAttempt saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\CakeDC\Users\Model\Entity\FailedPasswordAttempt>|\Cake\Datasource\ResultSetInterface<\CakeDC\Users\Model\Entity\FailedPasswordAttempt>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\CakeDC\Users\Model\Entity\FailedPasswordAttempt>|\Cake\Datasource\ResultSetInterface<\CakeDC\Users\Model\Entity\FailedPasswordAttempt> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\CakeDC\Users\Model\Entity\FailedPasswordAttempt>|\Cake\Datasource\ResultSetInterface<\CakeDC\Users\Model\Entity\FailedPasswordAttempt>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\CakeDC\Users\Model\Entity\FailedPasswordAttempt>|\Cake\Datasource\ResultSetInterface<\CakeDC\Users\Model\Entity\FailedPasswordAttempt> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class FailedPasswordAttemptsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('failed_password_attempts');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
            'className' => 'CakeDC/Users.Users',
        ]);
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
            ->uuid('user_id')
            ->notEmptyString('user_id');

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
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
