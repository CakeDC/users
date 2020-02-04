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

namespace CakeDC\Users\Test\TestCase\Model\Entity;

use Cake\Auth\DefaultPasswordHasher;
use Cake\I18n\I18n;
use Cake\I18n\Time;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Model\Entity\User;

/**
 * Users\Model\Entity\User Test Case
 */
class UserTest extends TestCase
{
    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->now = Time::now();
        Time::setTestNow($this->now);
        $this->User = new User();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->User);
        Time::setTestNow();

        parent::tearDown();
    }

    /**
     * Test tokenExpired method
     *
     * @return void
     */
    public function testTokenExpiredEmpty()
    {
        $this->User->token_expires = null;
        $isExpired = $this->User->tokenExpired();
        $this->assertTrue($isExpired);
    }

    /**
     * Test tokenExpired method
     *
     * @return void
     */
    public function testTokenExpiredNotYet()
    {
        $this->User->token_expires = '-1 day';
        $isExpired = $this->User->tokenExpired();
        $this->assertTrue($isExpired);
    }

    /**
     * Test tokenExpired method
     *
     * @return void
     */
    public function testTokenExpired()
    {
        $this->User->token_expires = '+1 day';
        $isExpired = $this->User->tokenExpired();
        $this->assertFalse($isExpired);
    }

    /**
     * Test tokenExpired another locale
     *
     * @return void
     */
    public function testTokenExpiredLocale()
    {
        I18n::setLocale('es_AR');
        $this->User->token_expires = '+1 day';
        $isExpired = $this->User->tokenExpired();
        $this->assertFalse($isExpired);
        $this->User->token_expires = '-1 day';
        $isExpired = $this->User->tokenExpired();
        $this->assertTrue($isExpired);
        I18n::setLocale('en_US');
    }

    /**
     * test
     *
     * @return void
     */
    public function testPasswordsAreEncrypted()
    {
        $pw = 'password';
        $this->User->password = $pw;
        $this->assertTrue((new DefaultPasswordHasher())->check($pw, $this->User->password));
    }

    public function testConfirmPasswordsAreEncrypted()
    {
        $pw = 'password';
        $this->User->confirm_password = $pw;
        $this->assertTrue((new DefaultPasswordHasher())->check($pw, $this->User->confirm_password));
    }

    /**
     * test
     *
     * @return void
     */
    public function testCheckPassword()
    {
        $pw = 'password';
        $this->assertTrue($this->User->checkPassword($pw, (new DefaultPasswordHasher())->hash($pw)));
        $this->assertFalse($this->User->checkPassword($pw, 'fail'));
    }

    /**
     * test
     *
     * @return void
     */
    public function testGetAvatar()
    {
        $this->assertNull($this->User->avatar);
        $avatar = 'first-avatar';
        $this->User->social_accounts = [
            ['avatar' => 'first-avatar'],
            ['avatar' => 'second-avatar'],
        ];
        $this->assertSame($avatar, $this->User->avatar);
    }

    /**
     * test
     *
     * @return void
     */
    public function testUpdateToken()
    {
        $this->assertNull($this->User['token']);
        $this->assertNull($this->User['token_expires']);
        $this->User->updateToken();
        $this->assertEquals($this->now, $this->User['token_expires']);
        $this->assertNotNull($this->User['token']);
    }

    /**
     * test
     *
     * @return void
     */
    public function testUpdateTokenExisting()
    {
        $this->User['token'] = 'aaa';
        $this->User['token_expires'] = $this->now;
        $this->User->updateToken();
        $this->assertEquals($this->now, $this->User['token_expires']);
        $this->assertNotEquals('aaa', $this->User['token']);
    }

    /**
     * test
     *
     * @return void
     */
    public function testUpdateTokenAdd()
    {
        $this->assertNull($this->User['token']);
        $this->assertNull($this->User['token_expires']);
        $this->User->updateToken(20);
        $this->assertEquals('20 seconds after', $this->User['token_expires']->diffForHumans($this->now));
        $this->assertNotNull($this->User['token']);
    }

    /**
     * test
     *
     * @return void
     */
    public function testUpdateTokenExistingAdd()
    {
        $this->User['token'] = 'aaa';
        $this->User['token_expires'] = $this->now;
        $this->User->updateToken(20);
        $this->assertEquals('20 seconds after', $this->User['token_expires']->diffForHumans($this->now));
        $this->assertNotEquals('aaa', $this->User['token']);
    }
}
