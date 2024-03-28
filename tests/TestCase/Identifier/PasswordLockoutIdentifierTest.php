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
    public function testIdentifyValidPasswordNotLocked()
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
    public function testIdentifyValidPasswordButLocked()
    {
        $password = Security::randomString();
        $AttemptsTable = TableRegistry::getTableLocator()->get('CakeDC/Users.FailedPasswordAttempts');
        $Table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $user = $Table->get('00000000-0000-0000-0000-000000000004');
        $currentCount = 5;
        $this->assertSame($currentCount, $AttemptsTable->find()->where(['user_id' => $user->id])->count());
        $user->password = $password;
        $Table->saveOrFail($user);
        $identifier = new PasswordLockoutIdentifier([
            'numberOfAttemptsFail' => 5,
        ]);
        $identity = $identifier->identify([
            PasswordIdentifier::CREDENTIAL_USERNAME => $user->username,
            PasswordIdentifier::CREDENTIAL_PASSWORD => $password,
        ]);
        $this->assertNull($identity);
        $this->assertSame($currentCount, $AttemptsTable->find()->where(['user_id' => $user->id])->count());
    }
}
