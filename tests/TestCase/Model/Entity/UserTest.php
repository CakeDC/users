<?php
namespace CakeDC\Users\Test\TestCase\Model\Entity;

use CakeDC\Users\Model\Entity\User;
use Cake\Auth\DefaultPasswordHasher;
use Cake\I18n\I18n;
use Cake\I18n\Time;
use Cake\TestSuite\TestCase;

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
    public function setUp()
    {
        parent::setUp();
        $this->User = new User();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->User);

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
        I18n::locale('es_AR');
        $this->User->token_expires = '+1 day';
        $isExpired = $this->User->tokenExpired();
        $this->assertFalse($isExpired);
        $this->User->token_expires = '-1 day';
        $isExpired = $this->User->tokenExpired();
        $this->assertTrue($isExpired);
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
        $this->assertTrue((new DefaultPasswordHasher)->check($pw, $this->User->password));
    }

    public function testConfirmPasswordsAreEncrypted()
    {
        $pw = 'password';
        $this->User->confirm_password = $pw;
        $this->assertTrue((new DefaultPasswordHasher)->check($pw, $this->User->confirm_password));
    }

    /**
     * test
     *
     * @return void
     */
    public function testCheckPassword()
    {
        $pw = 'password';
        $this->assertTrue($this->User->checkPassword($pw, (new DefaultPasswordHasher)->hash($pw)));
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
            ['avatar' => 'second-avatar']
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
        $now = Time::now();
        Time::setTestNow($now);
        $this->assertNull($this->User['token']);
        $this->assertNull($this->User['token_expires']);
        $this->User->updateToken();
        $this->assertEquals($now, $this->User['token_expires']);
        $this->assertNotNull($this->User['token']);
    }

    /**
     * test
     *
     * @return void
     */
    public function testUpdateTokenExisting()
    {
        $now = Time::now();
        Time::setTestNow($now);
        $this->User['token'] = 'aaa';
        $this->User['token_expires'] = $now;
        $this->User->updateToken();
        $this->assertEquals($now, $this->User['token_expires']);
        $this->assertNotEquals('aaa', $this->User['token']);
    }

    /**
     * test
     *
     * @return void
     */
    public function testUpdateTokenAdd()
    {
        $now = Time::now();
        Time::setTestNow($now);
        $this->assertNull($this->User['token']);
        $this->assertNull($this->User['token_expires']);
        $this->User->updateToken(20);
        $this->assertEquals($now->addSeconds(20), $this->User['token_expires']);
        $this->assertNotNull($this->User['token']);
    }

    /**
     * test
     *
     * @return void
     */
    public function testUpdateTokenExistingAdd()
    {
        $now = Time::now();
        Time::setTestNow($now);
        $this->User['token'] = 'aaa';
        $this->User['token_expires'] = $now;
        $this->User->updateToken(20);
        $this->assertEquals($now->addSecond(20), $this->User['token_expires']);
        $this->assertNotEquals('aaa', $this->User['token']);
    }
}
