<?php
namespace CakeDC\Users\Test\TestCase\Model\Entity;

use CakeDC\Users\Model\Entity\User;
use Cake\Auth\DefaultPasswordHasher;
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

    public function testCheckPassword()
    {
        $pw = 'password';
        $this->assertTrue($this->User->checkPassword($pw, (new DefaultPasswordHasher)->hash($pw)));
        $this->assertFalse($this->User->checkPassword($pw, 'fail'));
    }

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
}
