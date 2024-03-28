<?php

namespace CakeDC\Users\Test\TestCase\Identifier\PasswordLockout;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Security;
use CakeDC\Users\Identifier\PasswordLockout\LockoutHandler;

class LockoutHandlerTest extends TestCase
{
    /**
     * @inheritDoc
     */
    protected array $fixtures = [
        'plugin.CakeDC/Users.Users',
        'plugin.CakeDC/Users.FailedPasswordAttempts',
    ];

    /**
     * @return void
     */
    public function testNewFail()
    {
        $AttemptsTable = TableRegistry::getTableLocator()->get('CakeDC/Users.FailedPasswordAttempts');
        $id = '00000000-0000-0000-0000-000000000002';
        $handler = new LockoutHandler();
        $currentCount = 5;
        $attemptsBefore = $AttemptsTable->find()
            ->where(['user_id' => $id])
            ->orderByAsc('created')
            ->all()
            ->toArray();
        //First time will remove old records and still add a new one
        $handler->newFail($id);
        $this->assertSame($currentCount, count($attemptsBefore));
        $this->assertFalse($AttemptsTable->exists(['id' => $attemptsBefore[0]->id]));

        //Now only add a new one because there is nothing to remove
        $handler = new LockoutHandler();
        $handler->newFail($id);
        $attemptsAfterSecond = $AttemptsTable->find()->where(['user_id' => $id])->count();
        $this->assertSame($currentCount + 1, $attemptsAfterSecond);
    }

    /**
     * @return void
     */
    public function testIsUnlockedYes()
    {
        $handler = new LockoutHandler();
        $actual = $handler->isUnlocked( '00000000-0000-0000-0000-000000000002');
        $this->assertTrue($actual);
    }

    /**
     * @return void
     */
    public function testIsUnlockedNo()
    {
        $handler = new LockoutHandler();
        $actual = $handler->isUnlocked( '00000000-0000-0000-0000-000000000004');
        $this->assertFalse($actual);
    }

    /**
     * @return void
     */
    public function testIsUnlockedCompletedLockoutTime()
    {
        $handler = new LockoutHandler([
            'lockoutTimeInSeconds' => 60,
        ]);
        $actual = $handler->isUnlocked( '00000000-0000-0000-0000-000000000004');
        $this->assertTrue($actual);
    }
}
