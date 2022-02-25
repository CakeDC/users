<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2022, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2022, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace CakeDC\Users\Model\Table;

use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenTime;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use CakeDC\Auth\Authentication\Code2fAuthenticationCheckerInterface;
use CakeDC\Users\Exception\TokenExpiredException;
use CakeDC\Users\Mailer\SMSMailer;
use CakeDC\Users\Mailer\UsersMailer;
use CakeDC\Users\Model\Entity\OtpCode;
use CakeDC\Users\Plugin;

/**
 * OtpCodes Model
 *
 * @method \CakeDC\Users\Model\Entity\OtpCode newEmptyEntity()
 * @method \CakeDC\Users\Model\Entity\OtpCode newEntity(array $data, array $options = [])
 * @method \CakeDC\Users\Model\Entity\OtpCode[] newEntities(array $data, array $options = [])
 * @method \CakeDC\Users\Model\Entity\OtpCode get($primaryKey, $options = [])
 * @method \CakeDC\Users\Model\Entity\OtpCode findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \CakeDC\Users\Model\Entity\OtpCode patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \CakeDC\Users\Model\Entity\OtpCode[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \CakeDC\Users\Model\Entity\OtpCode|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \CakeDC\Users\Model\Entity\OtpCode saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \CakeDC\Users\Model\Entity\OtpCode[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \CakeDC\Users\Model\Entity\OtpCode[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \CakeDC\Users\Model\Entity\OtpCode[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \CakeDC\Users\Model\Entity\OtpCode[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OtpCodesTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('otp_codes');
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
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('code')
            ->maxLength('code', 255)
            ->requirePresence('code', 'create')
            ->notEmptyString('code');

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
        $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    public function sendCode2f($userId, $resend = false)
    {
        $user = $this->Users->get($userId);
        $new = false;
        try {
            if ($otpCode = $this->_getCurrent($userId)) {
                if (!$resend) return $otpCode;
            } else {
                $new = true;
                $otpCode = $this->_generateCode($userId);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw new \UnexpectedValueException(__d('cake_d_c/users', 'An error has occurred generating code. Please try again.'));
        }

        if (!$otpCode) {
            throw new RecordNotFoundException(__d('cake_d_c/users', 'Verification code could not be generated'));
        }
        if ($resend && !$new && (new FrozenTime())->diffInSeconds($otpCode->created) < 60) {
            throw new \OverflowException(__d('cake_d_c/users', 'You need to wait at least 60 seconds to request a new code'));
        }
        $type = Configure::read('Code2f.type');
        try {
            if ($type === Code2fAuthenticationCheckerInterface::CODE2F_TYPE_PHONE) {
                (new SMSMailer(Configure::read('Code2f.config', 'sms')))->otp($user, $otpCode->code);
            } elseif ($type === Code2fAuthenticationCheckerInterface::CODE2F_TYPE_EMAIL) {
                (new UsersMailer(Configure::read('Code2f.config', 'default')))->otp($user, $otpCode->code);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw new \UnexpectedValueException(__d('cake_d_c/users', 'An error has occurred sending code. Please try again.'));
        }

        return $otpCode;
    }

    /**
     * @param $userId
     * @return OtpCode
     * @throws \Exception
     */
    public function _generateCode($userId) {
        $key = random_int(0, 999999);
        $code = str_pad((string)$key, 6, '0', STR_PAD_LEFT);
        $otpCode = $this->newEntity([
            'code' => $code,
            'user_id' => $userId
        ]);
        return $this->save($otpCode);

    }

    protected function _getCurrent($userId)
    {
        /** @var OtpCode $otpCode */
        $otpCode = $this->find()->where(['user_id' => $userId, 'validated IS' => null])->orderDesc('created')->first();
        if (!$otpCode) {
            return false;
        }

        if ((new FrozenTime())->diffInSeconds($otpCode->created) > Configure::read('Code2f.maxSeconds') ||
            $otpCode->tries >= Configure::read('Code2f.maxTries')) {
            return false;
        }
        return $otpCode;
    }

    /**
     * @param $userId
     * @param $code
     * @return \CakeDC\Users\Model\Entity\OtpCode|false
     */
    public function validateCode2f($userId, $code): bool
    {
        $user = $this->Users->get($userId);
        $otpCode = $this->_getCurrent($userId);
        if (!$otpCode) {
            throw new TokenExpiredException(__d('cake_d_c/users', 'Verification code is expired or already validated. Please request a new one.'));
        }
        if ($otpCode->code !== $code) {
            $otpCode->tries += 1;
            $this->save($otpCode);
            throw new \InvalidArgumentException(__d('cake_d_c/users', 'Verification code is not valid. Please try again or request a new one.'));
        }
        if (Configure::read('Code2f.type') === Code2fAuthenticationCheckerInterface::CODE2F_TYPE_PHONE && !$user->phone_verified) {
            $user->phone_verified = new FrozenTime();
            if ($this->Users->save($user)) {
                $this->dispatchEvent(Plugin::EVENT_AFTER_PHONE_VERIFIED, ['user' => $user]);
            }
        }

        $otpCode->validated = new FrozenTime();
        return (bool)$this->save($otpCode);
    }
}
