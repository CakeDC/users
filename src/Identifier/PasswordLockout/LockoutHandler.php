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

namespace CakeDC\Users\Identifier\PasswordLockout;

use Cake\Core\InstanceConfigTrait;
use Cake\I18n\DateTime;
use Cake\ORM\Locator\LocatorAwareTrait;

class LockoutHandler implements LockoutHandlerInterface
{
    use InstanceConfigTrait;
    use LocatorAwareTrait;

    /**
     * Default configuration.
     *
     * @var array{timeWindowInSeconds: int, lockTimeInSeconds: int, numberOfAttemptsFail:int}
     */
    protected array $_defaultConfig = [
        'timeWindowInSeconds' =>  5 * 60,
        'lockTimeInSeconds' => 5 * 60,
        'numberOfAttemptsFail' => 6,
        'failedPasswordAttemptsModel' => 'CakeDC/Users.FailedPasswordAttempts'
    ];

    /**
     * Constructor
     *
     * @param array $config Configuration
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);
    }

    /**
     * @param string|int $id User's id
     * @return bool
     */
    public function isUnlocked(string|int $id): bool
    {
        $timeWindow = $this->getTimeWindow();
        $attempts = $this->getAttempts($id, $timeWindow);
        $attemptsCount = $attempts->count();
        $numberOfAttemptsFail = $this->getNumberOfAttemptsFail();

        if ($numberOfAttemptsFail > $attemptsCount) {
            return true;
        }

        $lockTime = $this->getLockTime();
        /**
         * @var \CakeDC\Users\Model\Entity\FailedPasswordAttempt $attempt
         */
        $attempt = $attempts->first();
        $lockTime = $attempt->created->addSeconds($lockTime);

        return $lockTime->isPast();
    }

    /**
     * @param string|int $id User's id
     *
     * @return void
     */
    public function newFail(string|int $id): void
    {
        $timeWindow = $this->getTimeWindow();
        $Table = $this->getTable();
        $entity = $Table->newEntity(['user_id' => $id]);
        $Table->saveOrFail($entity);
        $Table->deleteAll($Table->query()->newExpr()->lt('created', $timeWindow));
    }

    /**
     * @return \Cake\ORM\Table
     */
    protected function getTable(): \Cake\ORM\Table
    {
        return $this->getTableLocator()->get('CakeDC/Users.FailedPasswordAttempts');
    }

    /**
     * @param string|int $id
     * @param \Cake\I18n\DateTime $timeWindow
     * @return \Cake\Datasource\ResultSetInterface
     */
    protected function getAttempts(string|int $id, DateTime $timeWindow): \Cake\Datasource\ResultSetInterface
    {
        $query = $this->getTable()->find();

        return $query
            ->where([
                'user_id' => $id,
                $query->newExpr()->gte('created', $timeWindow)
            ])
            ->orderByDesc('created')
            ->all();
    }

    /**
     * @return \Cake\I18n\DateTime
     */
    protected function getTimeWindow(): DateTime
    {
        $timeWindow = $this->getConfig('timeWindowInSeconds');
        if (is_int($timeWindow) && $timeWindow >= 60) {
            return (new DateTime())->subSeconds($timeWindow);
        }

        throw new \UnexpectedValueException(__d('cake_d_c/users', 'Config "timeWindowInSeconds" must be integer greater than 60'));
    }

    /**
     * @return int
     */
    protected function getNumberOfAttemptsFail(): int
    {
        $number = $this->getConfig('numberOfAttemptsFail');
        if (is_int($number) && $number >= 1) {
            return $number;
        }
        throw new \UnexpectedValueException(__d('cake_d_c/users', 'Config "numberOfAttemptsFail" must be integer greater or equal 0'));
    }

    /**
     * @return int
     */
    protected function getLockTime(): int
    {
        $lockTime = $this->getConfig('lockTimeInSeconds');
        if (is_int($lockTime) && $lockTime >= 60) {
            return $lockTime;
        }

        throw new \UnexpectedValueException(__d('cake_d_c/users', 'Config "lockTimeInSeconds" must be integer greater than 60'));
    }
}
