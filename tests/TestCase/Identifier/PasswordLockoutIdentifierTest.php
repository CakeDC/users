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

namespace CakeDC\Users\Test\TestCase\Identifier;


use Authentication\Identifier\PasswordIdentifier;
use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Security;
use CakeDC\Users\Identifier\PasswordLockoutIdentifier;

class PasswordLockoutIdentifierTest extends TestCase
{
    /**
     * @inheritDoc
     */
    protected array $fixtures = [
        'plugin.CakeDC/Users.Users',
        'plugin.CakeDC/Users.FailedPasswordAttempts',
    ];

    /**
     * Test identify method with password and not locked
     *
     * @return void
     */
    public function testIdentifyOk()
    {
        $password = Security::randomString();
        $AttemptsTable = TableRegistry::getTableLocator()->get('CakeDC/Users.FailedPasswordAttempts');
        $Table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $user = $Table->get('00000000-0000-0000-0000-000000000002');
        $currentCount = 5;
        $this->assertSame($currentCount, $AttemptsTable->find()->where(['user_id' => $user->id])->count());
        $user->password = $password;
        $Table->saveOrFail($user);
        $identifier = new PasswordLockoutIdentifier();
        $identity = $identifier->identify([
            PasswordIdentifier::CREDENTIAL_USERNAME => $user->username,
            PasswordIdentifier::CREDENTIAL_PASSWORD => $password,
        ]);
        $this->assertInstanceOf(EntityInterface::class, $identity);
        $this->assertSame($currentCount, $AttemptsTable->find()->where(['user_id' => $user->id])->count());
    }

    /**
     * Test identify method with password and not locked
     *
     * @return void
     */
    public function testIdentifyValidPasswordButReachedMaxAttemptsAndLockTimeNotCompleted()
    {
        $password = Security::randomString();
        $AttemptsTable = TableRegistry::getTableLocator()->get('CakeDC/Users.FailedPasswordAttempts');
        $Table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $user = $Table->get('00000000-0000-0000-0000-000000000004');
        $currentCount = 6;
        $this->assertSame($currentCount, $AttemptsTable->find()->where(['user_id' => $user->id])->count());
        $user->password = $password;
        $Table->saveOrFail($user);
        $identifier = new PasswordLockoutIdentifier();
        $identity = $identifier->identify([
            PasswordIdentifier::CREDENTIAL_USERNAME => $user->username,
            PasswordIdentifier::CREDENTIAL_PASSWORD => $password,
        ]);
        $this->assertNull($identity);
        $this->assertSame($currentCount, $AttemptsTable->find()->where(['user_id' => $user->id])->count());
    }

    /**
     * Test identify method with password and not locked
     *
     * @return void
     */
    public function testIdentifyValidPasswordButReachedMaxAttemptsAndLockTimeAlreadyCompleted()
    {
        $password = Security::randomString();
        $AttemptsTable = TableRegistry::getTableLocator()->get('CakeDC/Users.FailedPasswordAttempts');
        $Table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $user = $Table->get('00000000-0000-0000-0000-000000000004');
        $currentCount = 6;
        $this->assertSame($currentCount, $AttemptsTable->find()->where(['user_id' => $user->id])->count());
        $user->password = $password;
        $Table->saveOrFail($user);
        $identifier = new PasswordLockoutIdentifier([
            'lockTimeInSeconds' => 60,
        ]);
        $identity = $identifier->identify([
            PasswordIdentifier::CREDENTIAL_USERNAME => $user->username,
            PasswordIdentifier::CREDENTIAL_PASSWORD => $password,
        ]);
        $this->assertInstanceOf(EntityInterface::class, $identity);
        $this->assertSame($currentCount, $AttemptsTable->find()->where(['user_id' => $user->id])->count());
    }

    /**
     * Test identify method with password and not locked
     *
     * @return void
     */
    public function testIdentifyInValidPasswordNotLockedBefore()
    {
        $password = Security::randomString();
        $AttemptsTable = TableRegistry::getTableLocator()->get('CakeDC/Users.FailedPasswordAttempts');
        $Table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $user = $Table->get('00000000-0000-0000-0000-000000000002');
        $currentCount = 5;
        $attemptsBefore = $AttemptsTable->find()
            ->where(['user_id' => $user->id])
            ->orderByAsc('created')
            ->all()
            ->toArray();
        $this->assertSame($currentCount, count($attemptsBefore));
        $wrongPassword = Security::randomString();
        $this->assertNotEquals($wrongPassword, $password);
        $user->password = $password;
        $Table->saveOrFail($user);
        $identifier = new PasswordLockoutIdentifier();
        $identity = $identifier->identify([
            PasswordIdentifier::CREDENTIAL_USERNAME => $user->username,
            PasswordIdentifier::CREDENTIAL_PASSWORD => $wrongPassword,//wrong password
        ]);
        //First call remove the first failed_password_attempt because is out of the window range and adds a new one
        $this->assertNull($identity);
        $this->assertFalse($AttemptsTable->exists(['id' => $attemptsBefore[0]->id]));
        $attemptsAfter = $AttemptsTable->find()->where(['user_id' => $user->id])->orderByAsc('created')->all()->toArray();
        $this->assertSame($currentCount, count($attemptsAfter));

        $identity = $identifier->identify([
            PasswordIdentifier::CREDENTIAL_USERNAME => $user->username,
            PasswordIdentifier::CREDENTIAL_PASSWORD => $wrongPassword,//wrong password
        ]);
        //On second call there is no record out of the window range only adds a new one
        $this->assertNull($identity);
        $this->assertTrue($AttemptsTable->exists(['id' => $attemptsAfter[0]->id]));
        $attemptsAfterSecond = $AttemptsTable->find()->where(['user_id' => $user->id])->count();
        $this->assertSame($currentCount + 1, $attemptsAfterSecond);
    }
}
