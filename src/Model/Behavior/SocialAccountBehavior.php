<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
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
use CakeDC\Users\Model\Entity\SocialAccount;

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
        if (!$this->table->hasAssociation('Users')) {
            $this->table
                ->belongsTo('Users')
                ->setForeignKey('user_id')
                ->setJoinType('INNER')
                ->setClassName(Configure::read('Users.table'));
        }
    }

    /**
     * After save callback
     *
     * @param \Cake\Event\EventInterface $event event
     * @param \Cake\Datasource\EntityInterface $entity entity
     * @param \ArrayObject $options options
     * @return mixed
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): mixed
    {
        if ($entity->get('active')) {
            $event->setResult(true);

            return null;
        }
        $user = $this->table->getAssociation('Users')->find()
            ->where(['Users.id' => $entity->get('user_id'), 'Users.active' => true])
            ->first();
        if (empty($user)) {
            $event->setResult(true);

            return null;
        }

        $event->setResult($this->sendSocialValidationEmail($entity, $user));

        return null;
    }

    /**
     * Send social validation email to the user
     *
     * @param \Cake\Datasource\EntityInterface $socialAccount social account
     * @param \Cake\Datasource\EntityInterface $user user
     * @return array
     */
    protected function sendSocialValidationEmail(EntityInterface $socialAccount, EntityInterface $user): array
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
     * @return \Cake\Datasource\EntityInterface
     */
    public function validateAccount(string $provider, string $reference, string $token): EntityInterface
    {
        $socialAccount = $this->table->find()
            ->select(['id', 'provider', 'reference', 'active', 'token'])
            ->where(['provider' => $provider, 'reference' => $reference])
            ->first();

        if (!empty($socialAccount) && $token !== '' && hash_equals((string)$socialAccount->get('token'), (string)$token)) {
            if ($socialAccount->get('active')) {
                throw new AccountAlreadyActiveException(__d('cake_d_c/users', 'Account already validated'));
            }
        } else {
            throw new RecordNotFoundException(
                __d('cake_d_c/users', 'Account not found for the given token and email.'),
            );
        }

        if (!$socialAccount instanceof SocialAccount) {
            throw new RecordNotFoundException(
                __d('cake_d_c/users', 'Account not found for the given token and email.'),
            );
        }

        return $this->activateAccount($socialAccount);
    }

    /**
     * Validates the social account
     *
     * @param string $provider provider
     * @param string $reference reference
     * @throws \Cake\Datasource\Exception\RecordNotFoundException
     * @throws \CakeDC\Users\Exception\AccountAlreadyActiveException
     * @return array
     */
    public function resendValidation(string $provider, string $reference): array
    {
        $socialAccount = $this->table->find()
            ->where(['provider' => $provider, 'reference' => $reference])
            ->contain('Users')
            ->first();

        if (!empty($socialAccount)) {
            if ($socialAccount->get('active')) {
                throw new AccountAlreadyActiveException(
                    __d('cake_d_c/users', 'Account already validated'),
                );
            }
        } else {
            throw new RecordNotFoundException(
                __d('cake_d_c/users', 'Account not found for the given token and email.'),
            );
        }

        return $this->sendSocialValidationEmail($socialAccount, $socialAccount->get('user'));
    }

    /**
     * Activates an account
     *
     * @param \CakeDC\Users\Model\Entity\SocialAccount $socialAccount social account
     * @return \Cake\Datasource\EntityInterface
     */
    protected function activateAccount(SocialAccount $socialAccount): EntityInterface
    {
        $socialAccount->set('active', true);

        return $this->table->saveOrFail($socialAccount);
    }
}
