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

use ArrayObject;
use CakeDC\Users\Email\EmailSender;
use CakeDC\Users\Exception\AccountAlreadyActiveException;
use CakeDC\Users\Model\Entity\SocialAccount;
use CakeDC\Users\Model\Entity\User;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\Mailer\Email;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;

/**
 * Covers social account features
 *
 */
class SocialAccountBehavior extends Behavior
{
    /**
     * Initialize, attaching belongsTo Users association
     *
     * @param array $config config
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->_table->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
            'className' => Configure::read('Users.table')
        ]);
        $this->Email = new EmailSender();
    }

    /**
     * After save callback
     *
     * @param Event $event event
     * @param Entity $entity entity
     * @param ArrayObject $options options
     * @return mixed
     */
    public function afterSave(Event $event, Entity $entity, $options)
    {
        if ($entity->active) {
            return true;
        }
        $user = $this->_table->Users->find()->where(['Users.id' => $entity->user_id, 'Users.active' => true])->first();
        if (empty($user)) {
            return true;
        }

        return $this->sendSocialValidationEmail($entity, $user);
    }

    /**
     * Send social validation email to the user
     *
     * @param EntityInterface $socialAccount social account
     * @param EntityInterface $user user
     * @param Email $email Email instance or null to use 'default' configuration
     * @return void
     */
    public function sendSocialValidationEmail(
        EntityInterface $socialAccount,
        EntityInterface $user,
        Email $email = null
    ) {
        $this->Email = new EmailSender();
        $this->Email->sendSocialValidationEmail($socialAccount, $user, $email);
    }

    /**
     * Validates the social account
     *
     * @param string $provider provider
     * @param string $reference reference
     * @param string $token token
     * @throws RecordNotFoundException
     * @throws AccountAlreadyActiveException
     * @return User
     */
    public function validateAccount($provider, $reference, $token)
    {
        $socialAccount = $this->_table->find()
            ->select(['id', 'provider', 'reference', 'active', 'token'])
            ->where(['provider' => $provider, 'reference' => $reference])
            ->first();

        if (!empty($socialAccount) && $socialAccount->token === $token) {
            if ($socialAccount->active) {
                throw new AccountAlreadyActiveException(__d('CakeDC/Users', "Account already validated"));
            }
        } else {
            throw new RecordNotFoundException(__d('CakeDC/Users', "Account not found for the given token and email."));
        }

        return $this->_activateAccount($socialAccount);
    }

    /**
     * Validates the social account
     *
     * @param string $provider provider
     * @param string $reference reference
     * @throws RecordNotFoundException
     * @throws AccountAlreadyActiveException
     * @return User
     */
    public function resendValidation($provider, $reference)
    {
        $socialAccount = $this->_table->find()
            ->where(['provider' => $provider, 'reference' => $reference])
            ->contain('Users')
            ->first();

        if (!empty($socialAccount)) {
            if ($socialAccount->active) {
                throw new AccountAlreadyActiveException(__d('CakeDC/Users', "Account already validated"));
            }
        } else {
            throw new RecordNotFoundException(__d('CakeDC/Users', "Account not found for the given token and email."));
        }

        return $this->sendSocialValidationEmail($socialAccount, $socialAccount->user);
    }

    /**
     * Activates an account
     *
     * @param SocialAccount $socialAccount social account
     * @return EntityInterface
     */
    protected function _activateAccount($socialAccount)
    {
        $socialAccount->active = true;
        $result = $this->_table->save($socialAccount);

        return $result;
    }
}
