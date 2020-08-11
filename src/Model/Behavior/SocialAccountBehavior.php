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

use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\Mailer\MailerAwareTrait;
use Cake\ORM\Behavior;
use CakeDC\Users\Exception\AccountAlreadyActiveException;

/**
 * Covers social account features
 */
class SocialAccountBehavior extends Behavior
{
    use MailerAwareTrait;

    /**
     * Initialize, attaching belongsTo Users association
     *
     * @param array $config config
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->_table->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
            'className' => Configure::read('Users.table'),
        ]);
    }

    /**
     * After save callback
     *
     * @param \Cake\Event\EventInterface $event event
     * @param \Cake\Datasource\EntityInterface $entity entity
     * @param \ArrayObject $options options
     * @return mixed
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        if ($entity->get('active')) {
            return true;
        }
        $user = $this->_table->getAssociation('Users')->find()
            ->where(['Users.id' => $entity->get('user_id'), 'Users.active' => true])
            ->first();
        if (empty($user)) {
            return true;
        }

        return $this->sendSocialValidationEmail($entity, $user);
    }

    /**
     * Send social validation email to the user
     *
     * @param \Cake\Datasource\EntityInterface $socialAccount social account
     * @param \Cake\Datasource\EntityInterface $user user
     * @return array
     */
    protected function sendSocialValidationEmail(EntityInterface $socialAccount, EntityInterface $user)
    {
        return $this
            ->getMailer(Configure::read('Users.Email.mailerClass') ?: 'CakeDC/Users.Users')
            ->send('socialAccountValidation', [$user, $socialAccount]);
    }

    /**
     * Validates the social account
     *
     * @param string $provider provider
     * @param string $reference reference
     * @param string $token token
     * @throws \Cake\Datasource\Exception\RecordNotFoundException
     * @throws \CakeDC\Users\Exception\AccountAlreadyActiveException
     * @return \CakeDC\Users\Model\Entity\User
     */
    public function validateAccount($provider, $reference, $token)
    {
        $socialAccount = $this->_table->find()
            ->select(['id', 'provider', 'reference', 'active', 'token'])
            ->where(['provider' => $provider, 'reference' => $reference])
            ->first();

        if (!empty($socialAccount) && $socialAccount->token === $token) {
            if ($socialAccount->active) {
                throw new AccountAlreadyActiveException(__d('cake_d_c/users', 'Account already validated'));
            }
        } else {
            throw new RecordNotFoundException(
                __d('cake_d_c/users', 'Account not found for the given token and email.')
            );
        }

        return $this->_activateAccount($socialAccount);
    }

    /**
     * Validates the social account
     *
     * @param string $provider provider
     * @param string $reference reference
     * @throws \Cake\Datasource\Exception\RecordNotFoundException
     * @throws \CakeDC\Users\Exception\AccountAlreadyActiveException
     * @return \CakeDC\Users\Model\Entity\User
     */
    public function resendValidation($provider, $reference)
    {
        $socialAccount = $this->_table->find()
            ->where(['provider' => $provider, 'reference' => $reference])
            ->contain('Users')
            ->first();

        if (!empty($socialAccount)) {
            if ($socialAccount->active) {
                throw new AccountAlreadyActiveException(
                    __d('cake_d_c/users', 'Account already validated')
                );
            }
        } else {
            throw new RecordNotFoundException(
                __d('cake_d_c/users', 'Account not found for the given token and email.')
            );
        }

        return $this->sendSocialValidationEmail($socialAccount, $socialAccount->user);
    }

    /**
     * Activates an account
     *
     * @param \CakeDC\Users\Model\Entity\SocialAccount $socialAccount social account
     * @return \Cake\Datasource\EntityInterface
     */
    protected function _activateAccount($socialAccount)
    {
        $socialAccount->active = true;
        $result = $this->_table->save($socialAccount);

        return $result;
    }
}
