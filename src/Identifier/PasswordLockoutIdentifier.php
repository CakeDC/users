<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2024, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2024, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Identifier;

use ArrayAccess;
use Authentication\Identifier\PasswordIdentifier;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;

class PasswordLockoutIdentifier extends PasswordIdentifier
{
    /**
     * @inheritDoc
     */
    protected function _checkPassword(ArrayAccess|array|null $identity, ?string $password): bool
    {
        $Table = TableRegistry::getTableLocator()->get('CakeDC/Users.FailedPasswordAttempts');
        $numberOfAttemptsFail = $this->getConfig('numberOfAttemptsFail', 6);
        $timeWindow = $this->getConfig('timeWindowInSeconds', 5 * 60);
        $lockTime = $this->getConfig('lockTimeInSeconds', 5 * 60);
        $timeWindow = (new DateTime())->subSeconds($timeWindow);

        $check = parent::_checkPassword($identity, $password);
        if (!$check) {
            $entity = $Table->newEntity(['user_id' => $identity['id']]);
            $Table->saveOrFail($entity);
            $Table->deleteAll($Table->query()->newExpr()->lt('created', $timeWindow));
        }
        $query = $Table->find();
        $attempts = $query
            ->where([
                'user_id' => $identity['id'],
                $query->newExpr()->gte('created', $timeWindow)
            ])
            ->orderByDesc('created')
            ->all();
        $attemptsCount = $attempts->count();
        if (!$check) {
            return false;
        }

        if ($numberOfAttemptsFail > $attemptsCount) {
            return true;
        }

        /**
         * @var \CakeDC\Users\Model\Entity\FailedPasswordAttempt $attempt
         */
        $attempt = $attempts->first();
        $lockTime = $attempt->created->addSeconds($lockTime);

        return $lockTime->isPast();
    }
}
